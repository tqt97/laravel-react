<?php

namespace App\Http\Controllers\Admin\Users;

use App\Actions\Admin\Users\BulkDeleteUsers;
use App\Actions\Admin\Users\BulkForceDeleteUsers;
use App\Actions\Admin\Users\BulkRestoreUsers;
use App\Actions\Admin\Users\CreateUser;
use App\Actions\Admin\Users\DeleteUser;
use App\Actions\Admin\Users\ForceDeleteUser;
use App\Actions\Admin\Users\RestoreUser;
use App\Actions\Admin\Users\UpdateUser;
use App\DTOs\Admin\Users\UserDTO;
use App\DTOs\Admin\Users\UserFilterDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Users\BulkDestroyUsersRequest;
use App\Http\Requests\Admin\Users\BulkForceDeleteUsersRequest;
use App\Http\Requests\Admin\Users\BulkRestoreUsersRequest;
use App\Http\Requests\Admin\Users\StoreUserRequest;
use App\Http\Requests\Admin\Users\UpdateUserRequest;
use App\Http\Requests\Admin\Users\UserIndexRequest;
use App\Http\Resources\Admin\UserResource;
use App\Models\User;
use App\Queries\Admin\Users\UserIndexQuery;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

final class UserController extends Controller
{
    public function index(
        UserIndexRequest $request,
        UserIndexQuery $query,
    ): Response|RedirectResponse {
        $filters = UserFilterDTO::fromRequest($request);
        $users = $query->execute($filters);

        if ($users->isEmpty() && $users->currentPage() > 1 && $users->lastPage() > 0) {
            return to_route('admin.users.index', [
                ...$this->listQuery($request),
                'page' => $users->lastPage(),
            ]);
        }

        return Inertia::render('admin/users/index', [
            'users' => UserResource::collection($users),
            'filters' => [
                'search' => $filters->search ?? '',
                'is_admin' => $filters->isAdmin,
                'locale' => $filters->locale->value ?? '',
                'timezone' => $filters->timezone ?? '',
                'sort' => $filters->sort,
                'direction' => $filters->direction,
                'per_page' => $filters->perPage,
                'trashed' => $filters->trashed,
            ],
        ]);
    }

    public function create(): Response
    {
        Gate::authorize('create', User::class);

        return Inertia::render('admin/users/create');
    }

    public function store(StoreUserRequest $request, CreateUser $action): RedirectResponse
    {
        $action->execute(UserDTO::fromRequest($request));

        Inertia::flash('toast', ['type' => 'success', 'message' => __('common.user_created')]);

        return to_route('admin.users.index');
    }

    public function edit(User $user): Response
    {
        Gate::authorize('view', $user);

        return Inertia::render('admin/users/edit', [
            'user' => UserResource::make($user),
        ]);
    }

    public function update(UpdateUserRequest $request, User $user, UpdateUser $action): RedirectResponse
    {
        $action->execute($request->user(), $user, UserDTO::fromRequest($request));

        Inertia::flash('toast', ['type' => 'success', 'message' => __('common.user_updated')]);

        return to_route('admin.users.index');
    }

    public function destroy(Request $request, User $user, DeleteUser $action): RedirectResponse
    {
        Gate::authorize('delete', $user);
        $action->execute($request->user(), $user);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('common.user_deleted')]);

        return to_route('admin.users.index', $this->listQuery($request));
    }

    public function restore(Request $request, User $user, RestoreUser $action): RedirectResponse
    {
        Gate::authorize('restore', $user);
        $action->execute($user);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('common.user_restored')]);

        return to_route('admin.users.index', $this->listQuery($request, ['trashed' => 'only']));
    }

    public function forceDestroy(Request $request, User $user, ForceDeleteUser $action): RedirectResponse
    {
        Gate::authorize('forceDelete', $user);
        $action->execute($user);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('common.user_force_deleted')]);

        return to_route('admin.users.index', $this->listQuery($request, ['trashed' => 'only']));
    }

    public function bulkDestroy(BulkDestroyUsersRequest $request, BulkDeleteUsers $action): RedirectResponse
    {
        $count = $action->execute($request->user(), $request->ids());

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => __('common.users_deleted_count', ['count' => $count]),
        ]);

        return to_route('admin.users.index', $this->listQuery($request));
    }

    public function bulkRestore(BulkRestoreUsersRequest $request, BulkRestoreUsers $action): RedirectResponse
    {
        $count = $action->execute($request->user(), $request->ids());

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => __('common.users_restored_count', ['count' => $count]),
        ]);

        return to_route('admin.users.index', $this->listQuery($request, ['trashed' => 'only']));
    }

    public function bulkForceDestroy(
        BulkForceDeleteUsersRequest $request,
        BulkForceDeleteUsers $action,
    ): RedirectResponse {
        $count = $action->execute($request->user(), $request->ids());

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => __('common.users_force_deleted_count', ['count' => $count]),
        ]);

        return to_route('admin.users.index', $this->listQuery($request, ['trashed' => 'only']));
    }

    /**
     * Keep the list context when a mutation redirects back to the index.
     * Only known list parameters are copied from the query string.
     *
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function listQuery(Request $request, array $overrides = []): array
    {
        $keys = [
            'search', 'is_admin', 'locale', 'timezone', 'trashed',
            'sort', 'direction', 'per_page', 'page',
        ];

        return array_replace($request->only($keys), $overrides);
    }
}
