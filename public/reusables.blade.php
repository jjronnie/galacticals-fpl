<x-page-title title="Dashboard" subtitle="Overview of system activities" />
<x-page-title title="Settings" />
<x-page-title />


<x-logo /> <!-- default size -->

<x-logo width="120" height="120" /> <!-- custom size -->

<x-logo width="80" height="80" class="rounded-full shadow-lg" /> <!-- with custom class -->



form

<x-form-input label="Role" name="role" required />
<x-form-input label="Email" name="email" type="email" />


<form action="" method="POST" enctype="multipart/form-data" class="space-y-4">
    @csrf
    @method('PUT')

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

        <div>
            <label class="label">Business Name *</label>
            <input type="text" name="name" value="" required class="input" />
        </div>

        <div>
            <label class="label">Business Name *</label>
            <input type="text" name="name" value="" required class="input" />
        </div>

    </div>

    <!-- Actions -->
    <div class="flex justify-end space-x-3">

        <!-- Confirmation Checkbox -->
        <x-confirmation-checkbox />

        <button type="submit" class="btn">
            Save <i data-lucide="save" class="w-4 h-4 ml-2"></i>
        </button>
    </div>

</form>


confirmation Modal

<x-confirm-modal :action="route('users.destroy', $user->id)"
    warning="Are you sure you want to delete this user? This action cannot be undone." triggerText="Delete User" />

<div class="grid grid-cols-1 sm:grid-cols-3 lg:grid-cols-4 gap-4 mb-8">
    <!-- card -->
    <x-stat-card title="" value="" icon="" color="" />

</div>


<!-- dropdown in table -->

<td>
    <x-dropdown-actions>

        <a href="{{ route('employees.show', $employee->id) }}" class="block px-4 py-2 hover:bg-gray-100 text-green-600"
            role="menuitem">View</a>

        <a href="{{ route('employees.edit', $employee->id) }}" class="block px-4 py-2 hover:bg-gray-100 text-blue-600"
            role="menuitem">Edit</a>

    </x-dropdown-actions>
</td>




<!-- Table -->

<x-table :headers="['#', ]" showActions="false">
    <x-table.row>
        <x-table.cell>1</x-table.cell>


        <x-table.cell>
            <x-dropdown-actions>
                <a href="#" class="block px-4 py-2 hover:bg-gray-100 text-green-600">View</a>
                <a href="#" class="block px-4 py-2 hover:bg-gray-100 text-blue-600">Edit</a>
            </x-dropdown-actions>
        </x-table.cell>
    </x-table.row>

</x-table>

<x-table :headers="[ ]" showActions="false">
    <x-table.row>
        <x-table.cell></x-table.cell>
    </x-table.row>
</x-table>

//status badge

<x-status-badge :status="$employee->status" />



side form-select
<x-slide-form button-text="Settings" title="Settings Form">

</x-slide-form>

<x-slide-form button-text="Settings" title="Settings Form">
    <form method="POST" action="">
        @csrf

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 ">

            <div>
                <label class="label">Business Name <span class="text-red-600"> *</span></label>
                <input type="text" name="name" value="{{ old('name', $business->name) }}" required class="input" />
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700">Name</label>
                <input type="text" name="name"
                    class="mt-1 block w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring focus:ring-green-500">
            </div>

        </div>
        <div class="pt-4">
            <button type="submit" class="btn">
                Save Changes
            </button>
        </div>
    </form>
</x-slide-form>


<!-- Default -->
<x-confirmation-checkbox />

<!-- Custom text -->
<x-confirmation-checkbox id="agree-terms" name="agree_terms" label="I agree to the Terms & Conditions" color="blue" />

<x-empty-state message="No products found for this business." />