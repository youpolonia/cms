# Content Scheduling User Guide

## Scheduling Interface

1. Open the content editor for any content item
2. The scheduling panel appears in the right sidebar
3. To create a new schedule:
   - Click "Add Schedule"
   - Select date/time using the datetime picker
   - Set priority (1 = highest, 5 = lowest)
   - Click "Save"

![Scheduling Interface](scheduling-interface.png)

## Timezone Handling
All scheduling times are:
- Displayed in your local timezone (detected from browser)
- Stored in UTC format
- Converted automatically for all users

## Conflict Resolution
When scheduling conflicts occur:
1. Higher priority schedules will execute first
2. Identical priority schedules will execute in chronological order
3. Conflicts are highlighted in the interface with warnings
4. You can manually resolve by:
   - Adjusting priorities
   - Changing publish times
   - Deleting conflicting schedules

## Schedule Statuses
- **Pending**: Scheduled but not yet processed
- **Processing**: Currently being published
- **Completed**: Successfully published
- **Failed**: Error during publishing (requires manual intervention)

## Best Practices
- Schedule during low-traffic periods for better performance
- Use higher priorities for time-sensitive content
- Review scheduled items weekly to ensure accuracy
- Test scheduling with draft content first