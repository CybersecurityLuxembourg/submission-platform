@props(['stats'])

<div class="mb-8 bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
    <div class="p-6 text-gray-900 dark:text-gray-100">
        <h3 class="text-lg font-semibold mb-4">System Statistics</h3>
        
        <!-- User Statistics -->
        <div class="mb-6">
            <h4 class="text-md font-medium mb-3">User Statistics</h4>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <x-statistics-card label="Total Users" :value="$stats['users']['total']" />
                <x-statistics-card label="Admins" :value="$stats['users']['admins']" />
                <x-statistics-card label="Internal Evaluators" :value="$stats['users']['internal_evaluators']" />
                <x-statistics-card label="External Evaluators" :value="$stats['users']['external_evaluators']" />
                <x-statistics-card label="Regular Users" :value="$stats['users']['regular_users']" />
                <x-statistics-card label="Users with 2FA" :value="$stats['users']['with_2fa']" />
                <x-statistics-card label="Unconfirmed Emails" :value="$stats['users']['unconfirmed_email']" />
            </div>
        </div>

        <!-- Form Statistics -->
        <div class="mb-6">
            <h4 class="text-md font-medium mb-3">Form Statistics</h4>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <x-statistics-card label="Total Forms" :value="$stats['forms']['total']" />
                <x-statistics-card label="Draft Forms" :value="$stats['forms']['draft']" />
                <x-statistics-card label="Published Forms" :value="$stats['forms']['published']" />
                <x-statistics-card label="Archived Forms" :value="$stats['forms']['archived']" />
            </div>
        </div>

        <!-- Submission Statistics -->
        <div class="mb-6">
            <h4 class="text-md font-medium mb-3">Submission Statistics</h4>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
                <x-statistics-card label="Total Submissions" :value="$stats['submissions']['total']" />
                <x-statistics-card label="Draft Submissions" :value="$stats['submissions']['draft']" />
                <x-statistics-card label="Submitted" :value="$stats['submissions']['submitted']" />
                <x-statistics-card label="Under Review" :value="$stats['submissions']['under_review']" />
                <x-statistics-card label="Completed" :value="$stats['submissions']['completed']" />
            </div>
        </div>
    </div>
</div> 