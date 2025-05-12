<?php

use App\Helpers\SystemDefine;
use App\Models\Document;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

// AUTH HELPERS
if (!function_exists('is_admin')) {
    function is_admin(User $user): bool
    {
        return @$user->department->slug === SystemDefine::ADMIN_DEPARTMENT;
    }
}

if (!function_exists('has_permission')) {
    function has_permission(User $user, string $slug): bool
    {
        if (is_admin($user)) {
            return true;
        }

        $permissionSlugs = [
            ...$user->department->permissions->pluck('slug')->toArray(),
            ...$user->permissions->pluck('slug')->toArray(),
        ];

        return in_array($slug, $permissionSlugs);
    }
}

if (!function_exists('can_access')) {
    function can_access(string $permissionSlug): bool
    {
        return !auth()->check()
            ? false
            : has_permission(auth()->user(), $permissionSlug . '_' . SystemDefine::ACCESS_PERMISSION);
    }
}

if (!function_exists('can_view')) {
    function can_view(string $permissionSlug): bool
    {
        return !auth()->check()
            ? false
            : has_permission(auth()->user(), $permissionSlug . '_' . SystemDefine::VIEW_PERMISSION);
    }
}

if (!function_exists('can_create')) {
    function can_create(string $permissionSlug): bool
    {
        return !auth()->check()
            ? false
            : has_permission(auth()->user(), $permissionSlug . '_' . SystemDefine::CREATE_PERMISSION);
    }
}

if (!function_exists('can_edit')) {
    function can_edit(string $permissionSlug): bool
    {
        return !auth()->check()
            ? false
            : has_permission(auth()->user(), $permissionSlug . '_' . SystemDefine::EDIT_PERMISSION);
    }
}

if (!function_exists('can_delete')) {
    function can_delete(string $permissionSlug): bool
    {
        return !auth()->check()
            ? false
            : has_permission(auth()->user(), $permissionSlug . '_' . SystemDefine::DELETE_PERMISSION);
    }
}
// END AUTH HELPERS

if (!function_exists('flash_message')) {
    function flash_message(string $message, string $type = 'success'): void
    {
        session()->flash("fl_{$type}", $message);
    }
}

if (!function_exists('generate_uniqid_code')) {
    function generate_uniqid_code(string $prefix = ''): string
    {
        return $prefix . strtoupper(bin2hex(random_bytes(4)));
    }
}

if (!function_exists('generate_slug')) {
    function generate_slug(string $name): string
    {
        return Str::slug($name) . '-' . generate_uniqid_code();
    }
}

if (!function_exists('store_file')) {
    function store_file(string $path, UploadedFile $file, string $alias, string $description = ''): Document
    {
        $document               = new Document();
        $document->alias        = $alias;
        $document->description  = $description;
        $document->name         = $file->getClientOriginalName();
        $document->size         = $file->getSize();
        $document->type         = $file->getType();
        $document->path         = $file->store($path . now()->format('Y-m-d'));
        return $document;
    }
}

if (!function_exists('get_string_day_by_int')) {
    function get_string_day_by_int(int $day): string
    {
        return @['Chủ nhật', 'Hai', 'Ba', 'Tư', 'Năm', 'Sáu', 'Bảy'][$day];
    }
}

if (!function_exists('get_type_value')) {
    function get_type_value(array $types, string $key): array
    {
        $arrTypes = $types;
        $arrReduceToType = array_reduce($arrTypes, function ($accumulator, $item) {
            $id = $item['id'];
            $name = $item['name'];
            $accumulator[$id] = ['id' => $id, 'name' => $name];
            return $accumulator;
        });
        return $arrReduceToType[$key];
    }
}
