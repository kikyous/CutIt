<?php
require __DIR__ . '/vendor/autoload.php';
use \Wa72\HtmlPageDom\HtmlPageCrawler;


$sites = [
    'cb' => [
        'index_filter' => '.module_list a',
        'show_filter' => 'article',
        'page' => function($page=1){
            return "http://m.cnbeta.com/list_latest_$page.htm";
        }],
    'rubychina' => [
        'index_filter' => '.title a',
        'show_filter' => '.col-md-9',
        'page' => function($page=1){
            return "https://ruby-china.org/topics?page=$page";
        }],
    'v2ex' => [
        'index_filter' => '.item_title a',
        'show_filter' => '.box',
        'page' => function($page=1){
            return "https://www.v2ex.com/recent?p=$page";
        }],
    'note4_tieba' => [
        'index_filter' => '.threadlist_text.threadlist_title a',
        'show_filter' => '.p_postlist',
        'page' => function($page=1){
            $pn = ($page-1)*50;
            return "http://tieba.baidu.com/f?kw=note4&ie=utf-8&pn=$pn";
        }],
];


function get(&$var, $default=null) {
    return isset($var) ? $var : $default;
}
function startsWith($haystack, $needle) {
    return $needle === "" || strrpos($haystack, $needle, -strlen($haystack)) !== FALSE;
}

$page = get($_GET['page'], 1);
$site = get($_GET['site']);
$show = get($_GET['show']);

$loader = new Twig_Loader_Filesystem( __DIR__ );
$twig = new Twig_Environment($loader, array());

if ($site){
    $_site = $sites[$site];
    $url_f = $_site['page'];
    if ($show){
        if (startsWith($show, 'http')) {
            $url = $show;
        } else {
            $url = $url_f(1);
            $url_array = parse_url($url);
            $url = $url_array['scheme'] . '://' . $url_array['host'] . $show;
            $content = new HtmlPageCrawler(file_get_contents($url));
            $content = $content->filter($_site['show_filter']);
            echo $twig->render('show.html', compact('site', 'content', 'url'));
        }
    } else {
        $url = $url_f($page);
        $content = new HtmlPageCrawler(file_get_contents($url));
        $list = $content->filter($_site['index_filter'])->each(function($node){
            return $node;
        });
        echo $twig->render('list.html', compact('list', 'page', 'site', 'url'));
    }

} else {
    echo $twig->render('sites.html', compact('sites'));
}
