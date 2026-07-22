<?php
declare(strict_types=1);

namespace App;

final class View
{
    public static function render(string $template, array $data = [], bool $printMode = false): void
    {
        extract($data, EXTR_SKIP);
        $site = $site ?? Config::site();
        $user = $user ?? Auth::currentUser();
        if ($user) {
            $user = Auth::publicUser($user);
        }
        $categories = $categories ?? Catalog::categories();
        $contentTemplate = Config::root() . '/templates/pages/' . $template . '.php';
        if (!is_file($contentTemplate)) {
            http_response_code(404);
            $contentTemplate = Config::root() . '/templates/pages/404.php';
        }
        ob_start();
        require $contentTemplate;
        $content = ob_get_clean();
        if ($printMode || !empty($raw)) {
            echo $content;
            return;
        }
        require Config::root() . '/templates/layouts/main.php';
    }
}
