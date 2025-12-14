<?php

$finder = (new PhpCsFixer\Finder())
    ->in(__DIR__)
    ->exclude('var')
;

return (new PhpCsFixer\Config())
    ->setRules([
        '@Symfony' => true,
        'align_multiline_comment' => true,
        'single_line_comment_spacing' => true,
        'cast_spaces' => ['space' => 'single'],
    ])
    ->setFinder($finder)
;
