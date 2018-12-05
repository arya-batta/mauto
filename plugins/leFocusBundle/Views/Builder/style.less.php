<?php

/*
 * @copyright   2016 Mautic, Inc. All rights reserved
 * @author      Mautic, Inc
 *
 * @link        https://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
if (!isset($preview)) {
    $preview = false;
}

ob_start();
?>
.mf-bar-iframe {
    z-index: 19000;
}

.mf-content {
    line-height: 1.1;

    .mf-inner-container {
        margin-top: 20px;
    }

    a.mf-link, .leform-button {
        padding: 5px 15px;
        -webkit-border-radius: 4px;
        -moz-border-radius: 4px;
        border-radius: 4px;
        cursor: pointer;
        text-align: center;
        text-decoration: none;
        border: none;
    }

    a.mf-link:hover, .leform-button:hover {
        opacity: 0.9;
        text-decoration: none;
        border: none;
    }
}

.le-focus {
    <?php if ($preview): ?>

    .leform-row {
        min-height: 0px;
    }
    <?php endif; ?>

    .leform_wrapper form {
        padding: 0;
        margin: 0;
    }

    .leform-input, select {
        border-radius: 2px;
        padding: 5px 8px;
        color: #757575;
        border: 1px solid #ababab;
    }

    .leform-input:focus, select:focus {
        outline: none;
        border: 1px solid #757575;
    }
}

<?php

echo $view->render(
    'leFocusBundle:Builder\Bar:style.less.php',
    [
        'preview' => $preview,
    ]
);

echo $view->render(
    'leFocusBundle:Builder\Modal:style.less.php',
    [
        'preview' => $preview,
    ]
);

echo $view->render(
    'leFocusBundle:Builder\Notification:style.less.php',
    [
        'preview' => $preview,
    ]
);

echo $view->render(
    'leFocusBundle:Builder\Page:style.less.php',
    [
        'preview' => $preview,
    ]
);

$less = ob_get_clean();

require_once __DIR__.'/../../Include/lessc.inc.php';
$compiler = new \lessc();
$css      = $compiler->compile($less);

if (empty($preview) && $app->getEnvironment() != 'dev') {
    $css = \Minify_CSS::minify($css);
}

echo $css;
