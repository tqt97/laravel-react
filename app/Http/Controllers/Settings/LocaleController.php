<?php

namespace App\Http\Controllers\Settings;

use App\Enums\Locale;
use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\LocaleUpdateRequest;
use App\Support\Locale\LocaleManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\App;
use Inertia\Inertia;

final class LocaleController extends Controller
{
    public function __invoke(LocaleUpdateRequest $request, LocaleManager $manager): RedirectResponse
    {
        $locale = Locale::from($request->validated('locale'));
        $user = $request->user();

        $manager->updateUser($user, $locale);

        App::setLocale($locale->value);
        $manager->syncRequest($request, $locale);

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => __('common.language_updated'),
        ]);

        return back();
    }
}
