<?php
namespace App\Controllers\Contribute;

use App\Core\{Controller, Session, Helper};
use App\Models\{ArticleModel, ContributorModel, CategoryModel};

class DashboardController extends Controller
{
    public function middleware(): void
    {
        if (!Session::get('contributor_id')) {
            Helper::redirect('/contribute/login');
        }
    }

    public function index(): void
    {
        $contributorId = Session::get('contributor_id');
        $articles      = new ArticleModel();

        $stats = [
            'total'     => $articles->countByContributor($contributorId),
            'published' => $articles->countByContributor($contributorId, 'published'),
            'review'    => $articles->countByContributor($contributorId, 'review'),
            'draft'     => $articles->countByContributor($contributorId, 'draft'),
        ];

        $recent     = $articles->byContributor($contributorId, 1, 8);
        $categories = (new ContributorModel())->assignedCategories($contributorId);

        $this->view('contribute.dashboard.index', [
            'pageTitle'  => 'My Dashboard',
            'stats'      => $stats,
            'recent'     => $recent['data'],
            'categories' => $categories,
        ], 'contributor');
    }
}
