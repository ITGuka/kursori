<?php
declare(strict_types=1);

namespace App\Controllers\Front;

use Core\Database\DB;
use Core\Http\Response;

final class HomeController
{
    public function __construct(private readonly DB $db)
    {
    }

    /** დროებითი გვერდი: ამოწმებს, რომ ბირთვი, ბაზა, თარგმანები და პარამეტრები მუშაობს */
    public function index(): Response
    {
        return view('front/home', [
            'title'    => setting('seo.meta_title') ?: setting('general.site_name'),
            'dbStatus' => 'MySQL ' . $this->db->value('SELECT VERSION()'),
        ]);
    }
}
