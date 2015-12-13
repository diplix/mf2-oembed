<?php
namespace oembed;
error_reporting(0);

$oembed_provider_formats = array( 'json', 'xml' );

$format = 'json';
if ( in_array( strtolower( $_GET['format'] ), $oembed_provider_formats ) ) {
	$format = $_GET['format'];
	}
if (isset($_GET['url'])) { $url = $_GET['url']; } else { 
	//header("HTTP/1.0 404 Not Found");
	echo 'no url provided'; 
	exit(); 
	}

$parse = parse_url($url);
$host = $parse['host'];

/*
 * lets parse the url
*/

require 'vendor/autoload.php';
use Mf2;

$mf = Mf2\fetch($url);

if (count($mf['items'])<1) {
	header("HTTP/1.0 404 Not Found");
	echo "nothing found";
	exit();
	}

foreach ($mf['items'] as $microformat) {
    //echo "A {$microformat['type'][0]} called {$microformat['properties']['name'][0]}\n";
	if ($microformat['type'][0] == "h-card") {
		$author_photo = $microformat['properties']['photo'][0]; 	
	}
	if (($microformat['type'][0] == "h-entry") OR ($microformat['type'][0] == "h-as-article")) {
		$hentryfound = 1;
	}
}

if (!$hentryfound) {
	header("HTTP/1.0 404 Not Found");
	echo "nothing found";
	exit();
}

$author_name = $mf['items'][0]['properties']['author'][0]['properties']['name'][0];
$author_url = $mf['items'][0]['properties']['author'][0]['properties']['url'][0];
$title = $mf['items'][0]['properties']['name'][0];

// needs better data!
$provider_name = $host;
$provider_url = "http://".$host;

$content_html = $mf['items'][0]['properties']['content'][0]['html'];
$content_txt = trim_text(strip_html_tags($content_html), 400, true); 

$photo = $mf['items'][0]['properties']['photo'][0]; 
$amphtml = $mf['rels']['amphtml'][0];

$data = array(
		'version'       => '1.0',
		'provider_name' => $provider_name,
		'provider_url'  => $provider_url,
		'author_name'   => $author_name,
		'author_url'    => $author_url,
		'title'         => $title,
		'type'          => 'rich',
	);
	
if (isset($photo)) { $cardsize = "large"; } 
elseif (isset($author_photo)) { $cardsize = "small"; } 
else {  $cardsize = "large";  }

$html = <<<EOD
<!DOCTYPE html>
<html >
  <head>
    <meta charset="utf-8">
	<meta name="robots" content="noindex,follow" />
	<link rel="canonical" href="$url" />

    <title>$provider_name embed: $title</title>
    <!--<base target="_blank">-->
        <style>
        html
		{
			font-family: sans-serif;
			-ms-text-size-adjust: 100%;
			-webkit-text-size-adjust: 100%;
		}

		body { margin: 0; }

		a
		{
			color: #0084b4;
			text-decoration: none;
		}

		a:focus,a:active,a:hover
		{
			color: #0084b4;
			text-decoration: underline;
		}
	
		.EmbedCard
		{
			color: #292F33;
			font-size: 14px;
			font-family: "Helvetica Neue",Helvetica,Arial,sans-serif;
			line-height: 1.3em;
		}

		.EmbedCard html
		{
			font-family: sans-serif;
			-ms-text-size-adjust: 100%;
			-webkit-text-size-adjust: 100%;
		}

		.EmbedCard body { margin: 0; }
		.EmbedCard a { color: black; }
		.EmbedCard a:focus { outline: thin dotted; }
		.EmbedCard a:active,.EmbedCard a:hover { 
			outline: 0; 
			text-decoration: none;
		}

		.EmbedCard h1
		{
			font-size: 2em;
			margin: .67em 0;
		}

		.EmbedCard .u-linkBlock,.EmbedCard .u-linkBlock:hover,.EmbedCard .u-linkBlock:focus,.EmbedCard .u-linkBlock:active
		{
			display: block;
			text-decoration: none;
		}

		.EmbedCard b,.EmbedCard strong { font-weight: 700; }
		.EmbedCard dfn { font-style: italic; }
		.EmbedCard mark
		{
			background: #ff0;
			color: #000;
		}
		.EmbedCard code,.EmbedCard kbd,.EmbedCard pre,.EmbedCard samp
		{
			font-family: monospace,serif;
			font-size: 1em;
		}

		.EmbedCard pre { white-space: pre-wrap; }
		.EmbedCard small { font-size: 80%; }

		.EmbedCard sub,.EmbedCard sup
		{
			font-size: 75%;
			line-height: 0;
			position: relative;
			vertical-align: baseline;
		}

		.EmbedCard sup { top: -.5em; }
		.EmbedCard sub { bottom: -.25em; }
		.EmbedCard img { border: 0; }
		.EmbedCard figure { margin: 0; }

		.EmbedCard .u-block { display: block!important; }

		.EmbedCard .u-textTruncate
		{
			max-width: 100%;
			overflow: hidden;
			text-overflow: ellipsis;
			white-space: nowrap;
			word-wrap: normal;
		}

		.EmbedCard
		{
			background: transparent;
			color: inherit;
			overflow: hidden;
		}

		.EmbedCard-title
		{
			font-size: 1em;
			margin: 0 0 .15em;
		}

		.EmbedCard-container
		{
			border-radius: .42857em;
			border-width: 1px;
			border-style: solid;
			border-color: #E1E8ED;
			box-sizing: border-box;
			color: inherit!important;
			overflow: hidden;
		}

		.EmbedCard-container--clickable
		{
			cursor: pointer;
			outline-offset: -1px;
			transition: background-color .15s ease-in-out,border-color .15s ease-in-out;
		}

		.EmbedCard-container--clickable:hover
		{
			background: #F5F8FA;
			border-color: rgba(136,153,166,.5);
		}

		.TwitterCardsGrid .TwitterCardsGrid-float--prev { float: left; }

		.TwitterCardsGrid .TwitterCardsGrid-col--spacerTopBottom,.TwitterCardsGrid-rtl .TwitterCardsGrid-col--spacerTopBottom
		{
			margin-top: .32333em;
			margin-bottom: .32333em;
		}

		.TwitterCardsGrid .TwitterCardsGrid-col--spacerTop,.TwitterCardsGrid-rtl .TwitterCardsGrid-col--spacerTop { margin-top: .32333em; }
		.TwitterCardsGrid .TwitterCardsGrid-col--spacerBottom,.TwitterCardsGrid-rtl .TwitterCardsGrid-col--spacerBottom { margin-bottom: .32333em; }

		.TwitterCardsGrid .TwitterCardsGrid-col--spacerNone,.TwitterCardsGrid-rtl .TwitterCardsGrid-col--spacerNone
		{
			margin-top: 0;
			margin-bottom: 0;
			margin-left: 0;
			margin-right: 0;
		}

		.tcu-linkImgFocus:focus
		{
			outline: 2px solid!important;
			outline-offset: -2px;
		}

		.tcu-linkImgFocus img { display: block; }
		.tcu-resetMargin { margin: 0; }
		.EmbedCard .showOnSuccess,.EmbedCard .showOnError { display: none; }
		.EmbedCard .successElements .showOnSuccess,.EmbedCard .errorElements .showOnError { display: block; }
		.EmbedCard .hideOnSuccess,.EmbedCard .hideOnError { display: block; }
		.EmbedCard .successElements .hideOnSuccess,.EmbedCard .errorElements .hideOnError { display: none; }
		.tcu-imageAspect--1to1:before { padding-top: 100%; }
		.tcu-imageAspect--1to2:before { padding-top: 200%; }
		.tcu-imageAspect--2to1:before { padding-top: 50%; }
		.tcu-imageAspect--1_91to1:before { padding-top: 52.25%; }
		.tcu-imageAspect--4to3:before { padding-top: 75%; }
		.tcu-imageAspect--5to2:before { padding-top: 40%; }
		.tcu-imageAspect--16to9:before { padding-top: 56.25%; }

		.tcu-imageContainer
		{
			position: relative;
			width: 100%;
			overflow: hidden;
		}

		.tcu-imageContainer:before
		{
			content: "";
			display: block;
		}

		.tcu-imageWrapper
		{
			position: absolute;
			top: -1px;
			left: -1px;
			bottom: -1px;
			right: -1px;
			background-size: cover;
			background-position: center;
			background-repeat: no-repeat;
		}

		.tcu-imageWrapper img
		{
			width: 100%;
			height: 100%;
			-ms-filter: "alpha(Opacity=0)";
			filter: alpha(opacity=0);
			opacity: 0;
		}

		.tcu-textEllipse--multiline
		{
			text-overflow: ellipsis;
		}

		.EmbedCard .EmbedCard-title
		{
			white-space: normal;
			max-height: 2.6em;
			padding-right: 1em;
		}

		.EmbedCard .SummaryCard-destination,
		.EmbedCard .SummaryCard-destination a
		{
			text-transform: lowercase;
			color: #8899A6;
			max-height: 1.3em;
			white-space: nowrap;
			overflow: hidden;
			text-overflow: ellipsis;
		}

		.EmbedCard .SummaryCard-content
		{
			padding: .75em;
			box-sizing: border-box;
			text-decoration: none;
		}

		.EmbedCard .SummaryCard-content .TwitterCard-title
		{
			max-height: 1.3em;
			white-space: nowrap;
			overflow: hidden;
			text-overflow: ellipsis;
		}

		.EmbedCard .SummaryCard-content p { overflow: hidden; }

		.EmbedCard .SummaryCard-image
		{
			/*background-color: #E1E8ED;*/
			/*border-style: solid;*/
			border-color: inherit;
			border-width: 0;
		}
		.EmbedCard .SummaryCard--large .SummaryCard-image { border-bottom-width: 1px; }

		.EmbedCard .SummaryCard--small .SummaryCard-contentContainer { width: calc(100% - 8.81667em - 1px); }

		.EmbedCard .SummaryCard--small .SummaryCard-image
		{
			width: 8.81667em;
			border-right-width: 1px;
		}
		.EmbedCard .SummaryCard--small .SummaryCard-image
		{
			width: 7.51667em;
			height: 7.51667em;
		}
		.EmbedCard .SummaryCard--small .SummaryCard-content p { max-height: 3.9em; max-height: 2.6em;}

		.EmbedCard .SummaryCard--large .SummaryCard-content
		{
			padding-left: 1em;
			padding-right: 1em;
		}

		.EmbedCard .SummaryCard--large .SummaryCard-content p { max-height: 2.6em; }

		.EmbedCard .SummaryCard--large .SummaryCard-content .Button--smallGray
		{
			float: right;
			margin-top: .25em;
		}

		.EmbedCard .SummaryCard-content p
		{
			max-height: 2.6em;
			/*white-space: nowrap;*/
			overflow: hidden;
			text-overflow: ellipsis;
		}

        </style>

    <meta name="card_name" content="summary_$cardsize_image" />
  </head>
  <body>
	<div class="TwitterCardsGrid EmbedCard" >
	<div class="TwitterCardsGrid-col--12 TwitterCardsGrid-col--spacerBottom CardContent">
    <div class="js-openLink u-block TwitterCardsGrid-col--12 EmbedCard-container EmbedCard-container--clickable SummaryCard--$cardsize" href="$url" target="_top">
EOD;

if (isset($photo)) { 
$html .= <<<EOD
	<div class="SummaryCard-image TwitterCardsGrid-col--12">
		<div class="tcu-imageContainer tcu-imageAspect--2to1">
			<div class="tcu-imageWrapper" style="background-image:url($photo);">
				<a href="$url"><img class="u-block" src="$photo" alt="Photo published for $title"></a>
			</div>
		</div>
	</div>
	<div class="SummaryCard-contentContainer TwitterCardsGrid-col--12">
EOD;
}

elseif (isset($author_photo)) {
$html .= <<<EOD
	<div class="SummaryCard-image TwitterCardsGrid-float--prev">
		<div class="tcu-imageContainer tcu-imageAspect--1to1">
			<div class="tcu-imageWrapper" style="background-image:url($author_photo);">
				<a href="$url"><img class="u-block" src="$author_photo" alt="Photo published for $title"></a>
			</div>
		</div>
	</div>
	<div class="SummaryCard-contentContainer TwitterCardsGrid-float--prev">
EOD;
}

else {
$html .= <<<EOD
	<div class="SummaryCard-contentContainer TwitterCardsGrid-col--12">
EOD;
}

$html .= <<<EOD
		<div class="SummaryCard-content">
			<h2 class="EmbedCard-title js-cardClick tcu-textEllipse--multiline"><a href="$url">$title</a></h2>

			<p class="tcu-resetMargin u-block TwitterCardsGrid-col--spacerTop tcu-textEllipse--multiline"><a href="$url">$content_txt</a></p>

			<span class="u-block TwitterCardsGrid-col--spacerTop SummaryCard-destination"><a href="$provider_url">$provider_name</a></span>

		</div>
	</div>

	</div>
	</div>
	</div>

	<script type="text/javascript">
		!function(a,b){"use strict";function c(b,c){a.parent.postMessage({message:b,value:c,secret:g},"*")}function d(){function d(){l.className=l.className.replace("hidden",""),b.querySelector('.wp-embed-share-tab-button [aria-selected="true"]').focus()}function e(){l.className+=" hidden",b.querySelector(".wp-embed-share-dialog-open").focus()}function f(a){var c=b.querySelector('.wp-embed-share-tab-button [aria-selected="true"]');c.setAttribute("aria-selected","false"),b.querySelector("#"+c.getAttribute("aria-controls")).setAttribute("aria-hidden","true"),a.target.setAttribute("aria-selected","true"),b.querySelector("#"+a.target.getAttribute("aria-controls")).setAttribute("aria-hidden","false")}function g(a){var c,d,e=a.target,f=e.parentElement.previousElementSibling,g=e.parentElement.nextElementSibling;if(37===a.keyCode)c=f;else{if(39!==a.keyCode)return!1;c=g}"rtl"===b.documentElement.getAttribute("dir")&&(c=c===f?g:f),c&&(d=c.firstElementChild,e.setAttribute("tabindex","-1"),e.setAttribute("aria-selected",!1),b.querySelector("#"+e.getAttribute("aria-controls")).setAttribute("aria-hidden","true"),d.setAttribute("tabindex","0"),d.setAttribute("aria-selected","true"),d.focus(),b.querySelector("#"+d.getAttribute("aria-controls")).setAttribute("aria-hidden","false"))}function h(a){var c=b.querySelector('.wp-embed-share-tab-button [aria-selected="true"]');n!==a.target||a.shiftKey?c===a.target&&a.shiftKey&&(n.focus(),a.preventDefault()):(c.focus(),a.preventDefault())}function i(a){var b,d=a.target;b=d.hasAttribute("href")?d.getAttribute("href"):d.parentElement.getAttribute("href"),c("link",b),a.preventDefault()}if(!k){k=!0;var j,l=b.querySelector(".wp-embed-share-dialog"),m=b.querySelector(".wp-embed-share-dialog-open"),n=b.querySelector(".wp-embed-share-dialog-close"),o=b.querySelectorAll(".wp-embed-share-input"),p=b.querySelectorAll(".wp-embed-share-tab-button button"),q=b.getElementsByTagName("a");if(o)for(j=0;j<o.length;j++)o[j].addEventListener("click",function(a){a.target.select()});if(m&&m.addEventListener("click",function(){d()}),n&&n.addEventListener("click",function(){e()}),p)for(j=0;j<p.length;j++)p[j].addEventListener("click",f),p[j].addEventListener("keydown",g);if(b.addEventListener("keydown",function(a){27===a.keyCode&&-1===l.className.indexOf("hidden")?e():9===a.keyCode&&h(a)},!1),a.self!==a.top)for(c("height",Math.ceil(b.body.getBoundingClientRect().height)),j=0;j<q.length;j++)q[j].addEventListener("click",i)}}function e(){a.self!==a.top&&(clearTimeout(i),i=setTimeout(function(){c("height",Math.ceil(b.body.getBoundingClientRect().height))},100))}function f(){a.self===a.top||g||(g=a.location.hash.replace(/.*secret=([\d\w]{10}).*/,"$1"),clearTimeout(h),h=setTimeout(function(){f()},100))}var g,h,i,j=b.querySelector&&a.addEventListener,k=!1;j&&(f(),b.documentElement.className=b.documentElement.className.replace(/\bno-js\b/,"")+" js",b.addEventListener("DOMContentLoaded",d,!1),a.addEventListener("load",d,!1),a.addEventListener("resize",e,!1))}(window,document);
	</script>

  </body>
</html>
EOD;

$width = 600;
$height = 373;
if ($cardsize == "small") { $height = 112; }

if (isset($_GET['maxwidth']) AND ($width > $_GET['maxwidth']) ) { $width = $_GET['maxwidth'];}
if (isset($_GET['maxheight']) AND ($height > $_GET['maxheight']) ) { $height = $_GET['maxheight'];}

$data['width'] = $width;
$data['height'] = $height;

$iframe = <<<EOD
<blockquote><p><a href="$url">$title</a></p><p><br>&nbsp;<br>$content_txt</p></blockquote>
EOD;
$iframe .= '<iframe sandbox="allow-top-navigation allow-popups-to-escape-sandbox allow-popups" security="restricted" src="'.$_SERVER['SCRIPT_URI'].'?url='.urlencode($url).'&render=1'.'" width="'.$width.'" height="'.$height.'" title="'.$title.'" frameborder="0" marginwidth="0" marginheight="0" scrolling="no" class=""></iframe>';

$data['html'] = $iframe;

/*
$data['html'] = <<<EOT
<p><strong>$provider_name: <a href="$url">...$title</a></strong></p>
<p>$content_txt</p>
EOT;
*/

/*
$data['type'] = "article";
$data['body'] = $content_txt;
$data['thumbnail_url'] = $photo;
$data['thumbnail_width'] = 100;
$data['thumbnail_height'] = 100;
*/

if ($_GET['render'] == 1) { 
	echo $html; 
} else { 
	header('Content-type: application/json');
	echo json_encode($data); 
}


function query_vars( $query_vars ) {
	$query_vars[] = 'oembed';
	$query_vars[] = 'format';
	$query_vars[] = 'url';
	$query_vars[] = 'callback';
	$query_vars[] = 'maxwidth';
	$query_vars[] = 'maxheight';
	$query_vars[] = 'pretty';
	return $query_vars;
}

/**
 * http://www.ebrueggeman.com/blog/abbreviate-text-without-cutting-words-in-half
 * trims text to a space then adds ellipses if desired
 * @param string $input text to trim
 * @param int $length in characters to trim to
 * @param bool $ellipses if ellipses (...) are to be added
 * @param bool $strip_html if html tags are to be stripped
 * @return string 
 */
function trim_text($input, $length, $ellipses = true, $strip_html = true) {
    //strip tags, if desired
    if ($strip_html) {
        $input = strip_tags($input);
    }
  
    //no need to trim, already shorter than trim length
    if (strlen($input) <= $length) {
        return $input;
    }
  
    //find last space within length
    $last_space = strrpos(substr($input, 0, $length), ' ');
    $trimmed_text = substr($input, 0, $last_space);
  
    //add ellipses (...)
    if ($ellipses) {
        $trimmed_text .= ' …';
    }
  
    return $trimmed_text;
}

/**
 * http://nadeausoftware.com/articles/2007/09/php_tip_how_strip_html_tags_web_page
 * Remove HTML tags, including invisible text such as style and
 * script code, and embedded objects.  Add line breaks around
 * block-level tags to prevent word joining after tag removal.
 */
function strip_html_tags( $text ) {
    $text = preg_replace(
        array(
          // Remove invisible content
            '@<head[^>]*?>.*?</head>@siu',
            '@<style[^>]*?>.*?</style>@siu',
            '@<script[^>]*?.*?</script>@siu',
            '@<object[^>]*?.*?</object>@siu',
            '@<embed[^>]*?.*?</embed>@siu',
            '@<applet[^>]*?.*?</applet>@siu',
            '@<noframes[^>]*?.*?</noframes>@siu',
            '@<noscript[^>]*?.*?</noscript>@siu',
            '@<noembed[^>]*?.*?</noembed>@siu',
          // Add line breaks before and after blocks
            '@</?((address)|(blockquote)|(center)|(del))@iu',
            '@</?((div)|(h[1-9])|(ins)|(isindex)|(p)|(pre))@iu',
            '@</?((dir)|(dl)|(dt)|(dd)|(li)|(menu)|(ol)|(ul))@iu',
            '@</?((table)|(th)|(td)|(caption))@iu',
            '@</?((form)|(button)|(fieldset)|(legend)|(input))@iu',
            '@</?((label)|(select)|(optgroup)|(option)|(textarea))@iu',
            '@</?((frameset)|(frame)|(iframe))@iu',
        ),
        array(
            ' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ',
            "\n\$0", "\n\$0", "\n\$0", "\n\$0", "\n\$0", "\n\$0",
            "\n\$0", "\n\$0",
        ),
        $text );
		//$text = strip_tags( $text, '<p><br>' );
		$text = strip_tags( $text );
        $text = preg_replace('/^[\pZ\pC]+|[\pZ\pC]+$/u','',$text);
    return trim( $text );
}