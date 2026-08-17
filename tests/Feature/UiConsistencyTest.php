<?php

it('keeps application UI sources on the shared UI token vocabulary', function (): void {
    $projectRoot = dirname(__DIR__, 2);
    $viewRoots = [
        $projectRoot.'/resources/views',
        $projectRoot.'/app-modules',
    ];
    $patterns = [
        'workspace compatibility utility' => '/(?<![\w-])workspace-[A-Za-z0-9_-]+/',
        'raw Tailwind palette class' => '/(?<![\w-])(?:[A-Za-z0-9-]+:)*(?:text|bg|border)-(?:slate|red|teal|amber|emerald)-[A-Za-z0-9_\.\/%\[\]-]+/',
        'raw color value' => '/(?:#[0-9A-Fa-f]{3,8}\b|(?:rgb|rgba|hsl|hsla)\([^)]*\))/',
        'inline presentation attribute' => '/\bstyle\s*=/i',
        'disallowed rounded corner' => '/(?<![\w-])(?:[A-Za-z0-9-]+:)*rounded-(?:xl|2xl|lg|md)/',
        'arbitrary typography utility' => '/(?<![\w-])(?:[A-Za-z0-9-]+:)*(?:text|tracking|leading|font)-\[[^\]\s]+\]/',
        'backdrop blur utility' => '/(?<![\w-])(?:[A-Za-z0-9-]+:)*backdrop-blur(?:-[A-Za-z0-9_\.\/%\[\]-]+)?/',
        'black font utility' => '/(?<![\w-])(?:[A-Za-z0-9-]+:)*font-black(?![\w-])/',
        'direct primitive token' => '/\bprimitive-[A-Za-z0-9_-]+/',
    ];
    $sourcePaths = [];

    foreach ($viewRoots as $viewRoot) {
        if (! is_dir($viewRoot)) {
            continue;
        }

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($viewRoot, FilesystemIterator::SKIP_DOTS),
        );

        foreach ($iterator as $file) {
            if (! $file->isFile() || ! str_ends_with($file->getFilename(), '.blade.php')) {
                continue;
            }

            $relativePath = ltrim(str_replace($projectRoot, '', $file->getPathname()), DIRECTORY_SEPARATOR);
            $isApplicationView = str_starts_with($relativePath, 'resources/views/');
            $isModuleView = str_starts_with($relativePath, 'app-modules/')
                && str_contains($relativePath, '/resources/views/');

            if ($isApplicationView || $isModuleView) {
                $sourcePaths[] = $file->getPathname();
            }
        }
    }

    $sourcePaths[] = $projectRoot.'/resources/css/app.css';
    sort($sourcePaths, SORT_STRING);

    $violations = [];

    foreach ($sourcePaths as $sourcePath) {
        $relativePath = ltrim(str_replace($projectRoot, '', $sourcePath), DIRECTORY_SEPARATOR);

        if ($relativePath === 'resources/views/welcome.blade.php') {
            continue;
        }

        $contents = file_get_contents($sourcePath);

        if ($contents === false) {
            continue;
        }

        $lines = preg_split('/\R/', $contents);

        if ($lines === false) {
            continue;
        }

        foreach ($lines as $line) {
            $linePatterns = $relativePath === 'resources/css/app.css'
                ? array_intersect_key($patterns, array_flip([
                    'workspace compatibility utility',
                    'black font utility',
                ]))
                : $patterns;

            foreach ($linePatterns as $patternName => $pattern) {
                preg_match_all($pattern, $line, $matches);

                foreach (array_unique($matches[0]) as $match) {
                    $violations[] = sprintf('%s: %s (%s)', $relativePath, $match, $patternName);
                }
            }
        }
    }

    expect($violations)->toBeEmpty(implode(PHP_EOL, $violations));
});
