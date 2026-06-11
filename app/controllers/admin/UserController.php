<?php
namespace App\Controllers\Admin;
use App\Core\{Controller, CSRF, Helper, Auth};
use App\Models\{UserModel, LocationModel, CategoryModel};

class UserController extends Controller
{
    protected function layout(): string
    {
        return \App\Core\Auth::role() === 'admin' ? 'admin' :
               (\App\Core\Auth::role() === 'chief_editor' ? 'editor_portal' : 'portal');
    }

    private UserModel $users;
    public function middleware(): void { $this->requireCan('manage_users'); }
    public function __construct() { $this->users = new UserModel(); }

    public function index(): void
    {
        $page   = max(1,(int)$this->get('page',1));
        $result = $this->users->allWithRoles($page, 20);
        $this->view('admin.users.index', [
            'pageTitle' => 'Users',
            'users'     => $result['data'],
            'total'     => $result['total'],
            'page'      => $result['page'],
            'per_page'  => $result['per_page'],
            'roles'     => $this->users->getRoles(),
            'badges'    => $this->users->getBadges(),
        ], $this->layout());
    }

    public function create(): void
    {
        $this->view('admin.users.form', [
            'pageTitle' => 'Add User',
            'user'      => [],
            'roles'     => $this->users->getRoles(),
            'districts' => (new LocationModel())->allDistricts(),
            'categories'=> (new CategoryModel())->topLevel(),
            'perms'     => [],
            'isEdit'    => false,
        ], $this->layout());
    }

    public function store(): void
    {
        CSRF::validate();
        $email = trim($this->post('email',''));
        if ($this->users->emailExists($email)) {
            $this->flash('danger','Email already exists.');
            $this->redirect('/admin/users/create');
        }
        $id = $this->users->insert([
            'role_id'               => (int)$this->post('role_id',3),
            'name'                  => Helper::sanitize($this->post('name','')),
            'email'                 => $email,
            'password'              => password_hash($this->post('password','secret123'), PASSWORD_BCRYPT),
            'is_active'             => 1,
            'is_blocked'            => 0,
            'assigned_district_id'  => (int)$this->post('assigned_district_id',0) ?: null,
            'assigned_category_ids' => $this->post('assigned_category_ids','') ?: null,
            'auto_approve'          => (int)(bool)$this->post('auto_approve',0),
        ]);
        $this->savePermissions((int)$id);
        $this->flash('success','User created.');
        $this->redirect('/admin/users');
    }

    public function edit(string $id): void
    {
        $user = $this->users->findWithRole((int)$id);
        if (!$user) { $this->flash('danger','Not found.'); $this->redirect('/admin/users'); }
        $this->view('admin.users.form', [
            'pageTitle'  => 'Edit: ' . $user['name'],
            'user'       => $user,
            'roles'      => $this->users->getRoles(),
            'districts'  => (new LocationModel())->allDistricts(),
            'categories' => (new CategoryModel())->topLevel(),
            'perms'      => $this->users->getPermissions((int)$id),
            'isEdit'     => true,
        ], $this->layout());
    }

    public function update(string $id): void
    {
        CSRF::validate();
        $data = [
            'role_id'               => (int)$this->post('role_id',3),
            'name'                  => Helper::sanitize($this->post('name','')),
            'email'                 => trim($this->post('email','')),
            'is_active'             => (int)(bool)$this->post('is_active',1),
            'assigned_district_id'  => (int)$this->post('assigned_district_id',0) ?: null,
            'assigned_category_ids' => $this->post('assigned_category_ids','') ?: null,
            'auto_approve'          => (int)(bool)$this->post('auto_approve',0),
        ];
        if ($pass = $this->post('password','')) {
            $data['password'] = password_hash($pass, PASSWORD_BCRYPT);
        }
        $this->users->update((int)$id, $data);
        $this->savePermissions((int)$id);
        $this->flash('success','User updated.');
        $this->redirect('/admin/users');
    }

    public function delete(string $id): void
    {
        CSRF::validate();
        $this->users->delete((int)$id);
        $this->flash('success','Deleted.');
        $this->redirect('/admin/users');
    }

    public function block(string $id): void
    {
        CSRF::validate();
        $this->users->block((int)$id);
        $this->flash('success','User blocked.');
        $this->redirect('/admin/users');
    }

    public function unblock(string $id): void
    {
        CSRF::validate();
        $this->users->unblock((int)$id);
        $this->flash('success','User unblocked.');
        $this->redirect('/admin/users');
    }

    public function assignBadge(string $id): void
    {
        CSRF::validate();
        $badgeId = (int)$this->post('badge_id',0);
        if ($badgeId) $this->users->assignBadge((int)$id, $badgeId);
        $this->flash('success','Badge assigned.');
        $this->redirect('/admin/users');
    }

    public function removePerm(string $id): void
    {
        CSRF::validate();
        $permId = (int)$this->post('perm_id',0);
        $this->users->removePermission($permId);
        if (Helper::isAjax()) { $this->json(['success'=>true]); }
        $this->redirect('/admin/users/edit/'.$id);
    }

    public function approveEdit(string $id): void
    {
        CSRF::validate();
        (new \App\Models\ArticleModel())->applyEdit((int)$id);
        $this->flash('success','Edit approved and applied.');
        $this->redirect('/admin/articles');
    }

    public function rejectEdit(string $id): void
    {
        CSRF::validate();
        (new \App\Models\ArticleModel())->rejectEdit((int)$id);
        $this->flash('success','Edit rejected.');
        $this->redirect('/admin/articles');
    }

    private function savePermissions(int $userId): void
    {
        $districtIds = (array)($this->post('perm_districts', []));
        $categoryIds = (array)($this->post('perm_categories', []));
        $canPublish  = (bool)$this->post('can_publish', 0);

        // Clear existing and re-add
        $this->db->prepare("DELETE FROM tn_editor_permissions WHERE user_id = ?")
            ->execute([$userId]);

        foreach ($districtIds as $did) {
            if ($did) $this->users->addPermission($userId, 'district', (int)$did, $canPublish);
        }
        foreach ($categoryIds as $cid) {
            if ($cid) $this->users->addPermission($userId, 'category', (int)$cid, $canPublish);
        }
    }

    public function removeBadge(string $userId): void
    {
        \App\Core\CSRF::validate();
        $badgeId = (int)$this->post('badge_id',0);
        try {
            \App\Core\Database::getInstance()->prepare(
                "DELETE FROM tn_user_badge_assignments WHERE user_id=? AND badge_id=?"
            )->execute([(int)$userId, $badgeId]);
        } catch (\Exception $e) {}
        $this->flash('success','Badge removed.');
        $this->redirect('/admin/users/edit/'.$userId);
    }


    public function promote(string $id): void
    {
        \App\Core\CSRF::validate();
        $this->requireCan('manage_users');
        $newRoleId = (int)$this->post('role_id', 0);
        if (!$newRoleId) {
            $this->flash('danger','Invalid role.');
            $this->redirect('/admin/users');
        }
        $this->users->promote((int)$id, $newRoleId);
        $this->flash('success','User role updated successfully.');
        $this->redirect('/admin/users');
    }

    public function toggleStatus(string $id): void
    {
        \App\Core\CSRF::validate();
        $this->requireCan('manage_users');
        $this->users->query(
            "UPDATE tn_users SET is_active = 1 - is_active WHERE id=?", [(int)$id]
        );
        $this->flash('success','User status toggled.');
        $this->redirect('/admin/users');
    }

}
