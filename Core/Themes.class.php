<?php
class Themes
{

    // inclure css
    public static function CSS($tab)
    {
        $css = "";
        for ($i = 0; $i < count($tab); $i++) {
            $code = htmlspecialchars(trim($tab[$i]));
            $css .= "<link rel='stylesheet' href='" . BASE_URL . "/Publics/CSS/" . $code . "?v=" . VERSION . "'>";
        }
        echo $css;
    }

    // pour inclure les portions de page
    public static function Template($template)
    {
        $template = htmlspecialchars(trim($template));
        include("Views/template-part/" . $template);
    }

    // pour inclure du JavaScript
    public static function Script($tab)
    {
        $script = "";
        for ($i = 0; $i < count($tab); $i++) {
            $code = htmlspecialchars(trim($tab[$i]));
            $script .= "<script src='" . BASE_URL . "/Publics/JS/" . $code . "?v=" . VERSION . "'></script>";
        }
        echo $script;
    }

    // pour inclure les utility CSS
    public static function loadCSS()
    {
        echo "
        <link rel='stylesheet' href='https://cdn.jsdelivr.net/npm/bootstrap@3.3.7/dist/css/bootstrap.min.css' integrity='sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u' crossorigin='anonymous'>
        <link rel='stylesheet' href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css' integrity='sha512-KfkfwYDsLkIlwQp6LFnl8zNdLGxu9YAA1QvwINks4PhcElQSvqcyVLLD9aMhXd13uQjoXtEKNosOWaZqXgel0g==' crossorigin='anonymous' referrerpolicy='no-referrer' />
        
        <link rel='stylesheet' href='https://unpkg.com/swiper@8/swiper-bundle.min.css'/>
        ";
        echo "\n";
    }

    // pour inclure les utility JS
    public static function loadJS()
    {
        echo '
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js" integrity="sha512-894YE6QWD5I59HgZOGReFYm4dnWc1Qt5NtvYSaNcOP+u1T9qYdvdihz0PPSiiqn/+/3e7Jo4EaG7TubfWGUrMQ==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/js/all.min.js" integrity="sha512-6PM0qYu5KExuNcKt5bURAoT6KCThUmHRewN3zUFNaoI6Di7XJPTMoT6K0nsagZKk2OB4L7E3q1uQKHNHd4stIQ==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
        
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@3.3.7/dist/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>
        <script src="https://cdn.tiny.cloud/1/l06ynxeds4qx56hp791skqu2sa71b2rzyhee9qj6e4opw7gm/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
        
        <script src="https://unpkg.com/swiper@8/swiper-bundle.min.js"></script>

        <script src='. BASE_URL . '/Publics/JS/ua-parser.min.js?v=' . VERSION . '></script>
        ';
        echo "\n";
    }

}
