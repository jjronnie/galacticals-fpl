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
        </div>


        {{-- Email Verified Switch --}}
        <div class="flex items-center gap-3 mt-4">
            <label for="email_verified" class="label cursor-pointer flex items-center gap-2">
                <span>Email Verified</span>
                <input type="checkbox" name="email_verified" id="email_verified" class="toggle-checkbox" {{
                    $user->hasVerifiedEmail() ? 'checked' : '' }}
                />
            </label>
        </div>






        <div class="flex justify-end space-x-3">
            <x-confirmation-checkbox />
            <button type="submit" class="btn">
                Update <i data-lucide="save" class="w-4 h-4 ml-2"></i>
            </button>
        </div>
    </form>

</x-slide-form>