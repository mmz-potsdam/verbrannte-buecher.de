<?php

$finder = (new PhpCsFixer\Finder())
    ->in(__DIR__)
    ->exclude([
        'vendor',
        'config',
        'var',
    ])
;

return (new PhpCsFixer\Config())
    ->setRules([
        '@PER-CS' => true,
        '@PHP82Migration' => true,
        'control_structure_continuation_position' => ['position' => 'next_line'],
        'elseif' => false, // don't change else if to elseif
        'visibility_required' => ['elements' =>
            // disable changing var into public
            [/*'const', 'method', 'property'*/]
        ],
    ])
    ->setFinder($finder)
;
