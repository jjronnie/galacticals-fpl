<x-slide-form button-icon="edit" title="Edit Staff">

    <form action="{{ route('admin.update', $user->id) }}" method="POST" enctype="multipart/form-data" class="space-y-4">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            {{-- Name --}}
            <div>
                <label for="name" class="label"> Name
                    <x-required-mark />
                </label>

                <input type="text" name="name" placeholder="Enter Full Name" value="{{ old('name', $user->name) }}"
                    required class="input @error('name') border-red-500 @enderror" />

                @error('name')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror

            </div>

            {{-- email --}}
            <div>
                <label for="email" class="label"> Email
                    <x-required-mark />
                </label>
                <input type="email" name="email" placeholder="Enter  email" value="{{ old('email', $user->email) }}"
                    required class="input @error('email') border-red-500 @enderror" />
                @error('email')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="label">Role
                    <x-required-mark />
                </label>
                <select name="role" required class="input">
                    <option value="admin" {{ $user->role === 'admin' ? 'selected' : '' }}>Admin</option>
                    <option value="user" {{ $user->role === 'user' ? 'selected' : '' }}>User
                    </option>
                </select>
            </div>




            <div>
                <label class="label">Status
                    <x-required-mark />
                </label>
                <select name="status" required class="input">
                    <option value="active" {{ $user->status === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="suspended" {{ $user->status === 'suspended' ? 'selected' : '' }}>Suspended
                    </option>
                </select>
            </div>

               {{-- sync_status --}}

        @if(optional($user->league)->sync_status)
    <div>
        <label for="sync_status" class="label">Sync Status</label>

        <input type="text"
               name="sync_status"
               placeholder="sync_status"
               value="{{ old('sync_status', optional($user->league)->sync_status) }}"
               class="input @error('sync_status') border-red-500 @enderror">

        @error('sync_status')
            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
        @enderror
    </div>
@endif

        </div>


     {{-- Email Verified At (Datetime Picker) --}}
<div class="mt-4">
    <label for="email_verified_at" class="label">
        Email Verified At
    </label>

    <input
        type="datetime-local"
        name="email_verified_at"
        id="email_verified_at"
        value="{{ old('email_verified_at', $user->email_verified_at ? $user->email_verified_at->format('Y-m-d\TH:i') : '') }}"
        class="input @error('email_verified_at') border-red-500 @enderror"
    >

    @error('email_verified_at')
        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
    @enderror
</div>







        <div class="flex justify-end space-x-3">
            <x-confirmation-checkbox />
            <button type="submit" class="btn">
                Update <i data-lucide="save" class="w-4 h-4 ml-2"></i>
            </button>
        </div>
    </form>

</x-slide-form>