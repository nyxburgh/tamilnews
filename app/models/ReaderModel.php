<?php
namespace App\Models;

use App\Core\Model;

class ReaderModel extends Model
{
    protected string $table = 'tn_readers';

    public function findByGoogle(string $googleId): array|false
    {
        return $this->fetchOne(
            "SELECT * FROM tn_readers WHERE google_id = ?",
            [$googleId]
        );
    }

    public function upsertFromGoogle(array $profile): int
    {
        $existing = $this->findByGoogle($profile['google_id']);
        if ($existing) {
            $this->update($existing['id'], [
                'name'       => $profile['name'],
                'avatar'     => $profile['avatar'],
                'last_login' => date('Y-m-d H:i:s'),
            ]);
            return $existing['id'];
        }
        return $this->insert([
            'google_id'  => $profile['google_id'],
            'name'       => $profile['name'],
            'email'      => $profile['email'],
            'avatar'     => $profile['avatar'],
            'last_login' => date('Y-m-d H:i:s'),
        ]);
    }
}
