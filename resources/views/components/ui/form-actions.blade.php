<div {{ $attributes->class(['flex flex-col gap-2 border-t border-slate-100 pt-5 sm:flex-row sm:flex-wrap sm:items-center [&>a]:w-full [&>button]:w-full [&>form]:w-full sm:[&>a]:w-auto sm:[&>button]:w-auto sm:[&>form]:w-auto [&>form>button]:w-full']) }}>
    {{ $slot }}
</div>
