<?php
declare(strict_types=1);

namespace App\Controllers\Front;

use Core\Database\DB;
use Core\Http\Response;
use Throwable;

final class HomeController
{
    public function __construct(private readonly DB $db)
    {
    }

    /** დროებითი გვერდი ფაზა 0-ისთვის: ამოწმებს, რომ ბირთვი და ბაზა მუშაობს */
    public function index(string $lang): Response
    {
        try {
            $languages = $this->db->select('SELECT code, native_name FROM languages WHERE is_active = 1 ORDER BY sort');
            $dbStatus = 'MySQL ' . $this->db->value('SELECT VERSION()');
        } catch (Throwable $e) {
            $languages = [];
            $dbStatus = 'DB error: ' . $e->getMessage();
        }

        return view('front/home', [
            'title'     => config('app.name'),
            'languages' => $languages,
            'dbStatus'  => $dbStatus,
        ]);
    }
}
