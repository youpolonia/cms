# Scheduling System UI Components Guide

## Schedule Editor
```javascript
<ScheduleEditor
  contentId="123"
  initialSchedule={null}
  onSave={(schedule) => console.log(schedule)}
/>
```

### Properties
- `contentId` (string, required): ID of content being scheduled
- `initialSchedule` (object): Existing schedule data to edit
- `onSave` (function): Callback when schedule is saved

## Schedule List
```javascript
<ScheduleList 
  items={schedules}
  onEdit={(id) => editSchedule(id)}
  onDelete={(id) => deleteSchedule(id)}
/>
```

### Properties
- `items` (array): List of scheduled items
- `onEdit` (function): Edit callback
- `onDelete` (function): Delete callback

## Recurrence Selector
```javascript
<RecurrenceSelector
  value={recurrence}
  onChange={(newRecurrence) => setRecurrence(newRecurrence)}
/>
```

### Styling
All components use CSS classes prefixed with `schedule-`:
```css
.schedule-editor { /* styles */ }
.schedule-list-item { /* styles */ }