<?php

foreach ($form as $i => $smartfield) {
    $isPrototype = ($smartfield->vars['name'] == '__name__');
    echo $view['form']->widget($smartfield, []);
}
