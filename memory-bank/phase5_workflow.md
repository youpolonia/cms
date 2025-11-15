# Schedule Approval Workflow

## Overview

The Schedule Approval Workflow provides a structured process for managing schedule status transitions with proper validation, notifications, and audit logging. This document outlines the implementation details, components, and flow of the approval process.

## Components

### 1. API Endpoint

- **Path**: `/api/schedules/approve`
- **Method**: POST
- **Authentication**: Required via WorkerAuthenticate middleware
- **Permission**: `schedule_management`

### 2. State Machine Logic

The workflow implements a state machine for schedule status transitions with the following valid state transitions:

```
scheduled → approved, rejected, cancelled
pending → approved, rejected, cancelled
approved → completed, cancelled
rejected → scheduled, pending
cancelled → scheduled, pending
completed → (terminal state)
```

Any attempt to transition to an invalid state will be rejected with an appropriate error message.

### 3. Notification System

The workflow includes a comprehensive notification system that sends:

- **In-app notifications**: Stored in the database for display in the user interface
- **Email notifications**: HTML and plain text emails with status details
- **SMS notifications**: Concise text messages for urgent updates

### 4. Audit Logging

All status changes are logged with:
- Previous status
- New status
- Timestamp
- User who made the change
- Reason for the change (if provided)

## Implementation Details

### API Endpoint Implementation

The API endpoint (`/api/schedules/approve`) handles:
1. Authentication and permission validation
2. Input validation
3. Current shift data retrieval
4. Status transition validation
5. Shift status update
6. Audit logging
7. Notification generation and delivery

### WorkerController Implementation

The `WorkerController::updateScheduleStatus()` method implements the state machine logic:
1. Validates input data
2. Retrieves current shift data
3. Validates the requested status transition
4. Updates the shift status
5. Logs the status change
6. Sends notifications

### Notification Templates

The system includes templates for:
1. **Email notifications**:
   - HTML version with styling and formatting
   - Plain text version for email clients that don't support HTML

2. **SMS notifications**:
   - Concise format with essential information
   - Character-count optimized

## Usage

### Request Format

```json
{
  "shift_id": 123,
  "status": "approved",
  "reason": "Schedule confirmed by manager"
}
```

### Response Format

```json
{
  "success": true,
  "message": "Shift status updated to approved",
  "shift_id": 123,
  "status": "approved"
}
```

## Error Handling

The workflow includes comprehensive error handling:

1. **Authentication errors**: 401 Unauthorized
2. **Permission errors**: 403 Forbidden
3. **Input validation errors**: 400 Bad Request
4. **Not found errors**: 404 Not Found
5. **Invalid state transition errors**: 400 Bad Request
6. **Database errors**: 500 Internal Server Error

## Security Considerations

1. **Authentication**: All requests require valid authentication
2. **Authorization**: Permission-based access control
3. **Input validation**: All inputs are validated
4. **Audit logging**: All actions are logged for accountability

## Future Enhancements

1. **Approval chains**: Support for multi-level approvals
2. **Bulk approvals**: Support for approving multiple schedules at once
3. **Approval rules**: Configurable rules for automatic approvals
4. **Mobile app notifications**: Push notifications for mobile devices