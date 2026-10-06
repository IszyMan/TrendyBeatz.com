@php
    $editing = $managedUser !== null;
@endphp

<form
    class="admin-panel admin-form-grid"
    method="POST"
    action="{{ $editing
        ? route('admin.users.update', $managedUser->id)
        : route('admin.users.store') }}"
>
    @csrf

    @if ($editing)
        @method('PUT')
    @endif

    <label class="admin-field">
        Name

        <input
            type="text"
            name="name"
            maxlength="191"
            value="{{ old('name', $managedUser->name ?? '') }}"
            required
        >
    </label>

    <label class="admin-field">
        Username

        <input
            type="text"
            name="username"
            maxlength="191"
            value="{{ old('username', $managedUser->username ?? '') }}"
            pattern="[a-zA-Z0-9_.-]+"
            required
        >

        <small>Letters, numbers, underscores, dots, and hyphens.</small>
    </label>

    <label class="admin-field">
        Email

        <input
            type="email"
            name="email"
            maxlength="191"
            value="{{ old('email', $managedUser->email ?? '') }}"
            required
        >
    </label>

    <label class="admin-field">
        Phone number

        <input
            type="tel"
            name="phone_number"
            maxlength="191"
            value="{{ old('phone_number', $managedUser->phone_number ?? '') }}"
        >
    </label>

    <label class="admin-field">
        {{ $editing ? 'New password (optional)' : 'Password' }}

        <input
            type="password"
            name="password"
            minlength="12"
            maxlength="72"
            autocomplete="new-password"
            @required(!$editing)
        >

        <small>
            {{ $editing
                ? 'Leave blank to keep the existing password.'
                : 'Use at least 12 characters.' }}
        </small>
    </label>

    <label class="admin-field">
        Confirm password

        <input
            type="password"
            name="password_confirmation"
            minlength="12"
            maxlength="72"
            autocomplete="new-password"
            @required(!$editing)
        >
    </label>

    <label class="admin-field">
        Role

        <select name="roleid" required>
            @foreach ($roles as $id => $label)
                <option
                    value="{{ $id }}"
                    @selected(
                        (int) old(
                            'roleid',
                            $managedUser->roleid ?? config('admin.editor')
                        ) === (int) $id
                    )
                >
                    {{ $label }}
                </option>
            @endforeach
        </select>
    </label>

    <label class="admin-field">
        Account status

        <select name="is_active" required>
            <option
                value="1"
                @selected((int) old('is_active', $managedUser->is_active ?? 1) === 1)
            >
                Active
            </option>

            <option
                value="0"
                @selected((int) old('is_active', $managedUser->is_active ?? 1) === 0)
            >
                Inactive
            </option>
        </select>
    </label>

    <div class="admin-field-wide admin-actions">
        <button class="admin-button" type="submit">
            {{ $editing ? 'Save Changes' : 'Create User' }}
        </button>

        <a class="admin-button" href="{{ route('admin.users.index') }}">
            Cancel
        </a>
    </div>
</form>