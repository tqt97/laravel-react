<?php

namespace App\Http\Controllers\Settings;

use App\Actions\Settings\UpdateLocale;
use App\Enums\Locale;
use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\LocaleUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cookie;
use Inertia\Inertia;

final class LocaleController extends Controller
{
    public function __invoke(LocaleUpdateRequest $request, UpdateLocale $action): RedirectResponse
    {
        $locale = Locale::from($request->validated('locale'));

        if ($request->user()) {
            $action->execute($request->user(), $locale);
        }

        $request->session()->put('locale', $locale->value);

        App::setLocale($locale->value);
        Cookie::queue(config('locale.cookie.name'), $locale->value, config('locale.cookie.minutes'));

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => __('common.language_updated'),
        ]);

        return back();
    }
}
