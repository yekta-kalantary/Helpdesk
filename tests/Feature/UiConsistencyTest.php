<?php

it('keeps application blade views on the shared UI token vocabulary', function (): void {
    $projectRoot = dirname(__DIR__, 2);
    $viewRoots = [
        $projectRoot.'/resources/views',
        $projectRoot.'/app-modules',
    ];
    $roundedCornerAllowlist = [];
    $patterns = [
        'workspace compatibility utility' => '/(?<![\w-])workspace-[A-Za-z0-9_-]+/',
        'raw Tailwind palette class' => '/(?<![\w-])(?:[A-Za-z0-9-]+:)*(?:text|bg|border)-(?:slate|red|teal|amber|emerald)-[A-Za-z0-9_\.\/%\[\]-]+/',
        'raw color value' => '/(?:#[0-9A-Fa-f]{3,8}\b|(?:rgb|rgba|hsl|hsla)\([^)]*\))/',
        'inline presentation attribute' => '/\bstyle\s*=/i',
        'disallowed rounded corner' => '/(?<![\w-])(?:[A-Za-z0-9-]+:)*rounded-(?:xl|2xl|lg|md)/',
        'direct primitive token' => '/\bprimitive-[A-Za-z0-9_-]+/',
    ];
    $viewPaths = [];

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
                $viewPaths[] = $file->getPathname();
            }
        }
    }

    sort($viewPaths, SORT_STRING);

    $violations = [];

    foreach ($viewPaths as $viewPath) {
        $relativePath = ltrim(str_replace($projectRoot, '', $viewPath), DIRECTORY_SEPARATOR);

        if ($relativePath === 'resources/views/welcome.blade.php') {
            continue;
        }

        $contents = file_get_contents($viewPath);

        foreach ($patterns as $patternName => $pattern) {
            preg_match_all($pattern, $contents, $matches);

            foreach (array_unique($matches[0]) as $match) {
                if ($patternName === 'disallowed rounded corner'
                    && in_array($match, $roundedCornerAllowlist[$relativePath] ?? [], true)) {
                    continue;
                }

                $violations[] = sprintf('%s: %s (%s)', $relativePath, $match, $patternName);
            }
        }
    }

    expect($violations)->toBeEmpty(implode(PHP_EOL, $violations));
});
