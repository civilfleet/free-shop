<div>
    <div class="mb-3">
        <div class="input-group">
            <input
                type="search"
                wire:model.debounce.500ms="search"
                placeholder="Search users..."
                wire:keydown.escape="$set('search', '')"
                class="form-control"/>
            <div class="input-group-append" >
                <span class="input-group-text" wire:loading wire:target="search">
                    <x-spinner/>
                </span>
                @empty($search)
                    <span class="input-group-text" wire:loading.remove wire:target="search">
                        {{ $users->total() }} total
                    </span>
                @else
                    <span class="input-group-text @if($users->isEmpty()) bg-warning @else bg-success text-light @endif" wire:loading.remove wire:target="search">
                        {{ $users->total() }} results
                    </span>
                @endif
            </div>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table table-bordered bg-white shadow-sm">
            <thead>
                <th colspan="2">Name</th>
                <th>E-Mail</th>
                <th>Last login</th>
            </thead>
            <tbody>
                @forelse($users as $user)
                    <tr >
                        <td class="fit">
                            @isset($user->avatar)
                                <img
                                    src="{{ $user->avatar }}"
                                    alt="Avatar"
                                    class="align-top rounded-circle"
                                    height="24"
                                    width="24">
                            @endisset
                        </td>
                        <td>{{ $user->name }}</td>
                        <td>
                            <a href="mailto:{{ $user->email }}">{{ $user->email }}</a>
                        </td>
                        <td><x-date-time-info :value="$user->last_login_at"/></td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="text-center">
                            <em>
                                @if(filled($search))
                                    No users found for term '{{ $search }}'.
                                @else
                                    No users found.
                                @endif
                            </em>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $users->links() }}
</div>
