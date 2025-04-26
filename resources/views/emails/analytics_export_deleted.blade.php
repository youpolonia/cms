@component('mail::message')
# Analytics Export Deleted

Your analytics export file **{{ $fileName }}** has been deleted as part of our automated cleanup process.

This is an automated notification - no action is required on your part.

@component('mail::button', ['url' => route('exports.index')])
View Exports
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent
