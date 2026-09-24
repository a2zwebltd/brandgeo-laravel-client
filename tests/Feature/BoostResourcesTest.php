<?php

$skillsPath = dirname(__DIR__, 2).'/resources/boost/skills';

it('ships the brandgeo-sdk Boost skill', function () use ($skillsPath) {
    expect("{$skillsPath}/brandgeo-sdk/SKILL.md")->toBeFile();
});

it('gives every Boost skill frontmatter whose name matches its folder', function () use ($skillsPath) {
    $skills = glob("{$skillsPath}/*/SKILL.md");

    expect($skills)->not->toBeEmpty();

    foreach ($skills as $file) {
        $contents = file_get_contents($file);

        expect(preg_match('/\A---\R(.*?)\R---\R/s', $contents, $frontmatter))->toBe(1, "{$file} has no frontmatter");
        expect(preg_match('/^name:\s*(\S+)\s*$/m', $frontmatter[1], $name))->toBe(1);
        expect(preg_match('/^description:\s*(.+)$/m', $frontmatter[1], $description))->toBe(1);

        expect($name[1])->toBe(basename(dirname($file)))
            ->and(trim($description[1]))->not->toBeEmpty();
    }
});
