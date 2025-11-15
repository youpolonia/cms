# Notification System Documentation

## Overview
The notification system provides a rules-based engine for triggering and delivering notifications across multiple channels. It supports:
- Event-based triggers
- Conditional logic
- Multi-tenant isolation
- Multiple delivery channels (email, SMS, in-app)

## Database Schema

### Notification Rules (`notification_rules`)
- Stores core rule definitions
- Tenant-isolated
- Contains JSON fields for conditions and actions

```sql
CREATE TABLE notification_rules (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT NOT NULL,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    conditions JSON,
    actions JSON,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_tenant (tenant_id)
);
```

### Notification Triggers (`notification_triggers`)
- Defines events that can trigger rules
- Links to parent rule

```sql
CREATE TABLE notification_triggers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT NOT NULL,
    rule_id INT NOT NULL,
    event_type VARCHAR(100) NOT NULL,
    trigger_params JSON,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_tenant (tenant_id),
    INDEX idx_rule (rule_id),
    INDEX idx_event_type (event_type),
    CONSTRAINT fk_triggers_rule FOREIGN KEY (rule_id) 
        REFERENCES notification_rules(id) ON DELETE CASCADE
);
```

### Notification Conditions (`notification_conditions`)
- Stores individual condition logic
- Supports logical operators and grouping

```sql
CREATE TABLE notification_conditions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT NOT NULL,
    rule_id INT NOT NULL,
    field VARCHAR(100) NOT NULL,
    operator VARCHAR(20) NOT NULL,
    value TEXT,
    logical_operator VARCHAR(3),
    condition_group INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_tenant (tenant_id),
    INDEX idx_rule (rule_id),
    CONSTRAINT fk_conditions_rule FOREIGN KEY (rule_id) 
        REFERENCES notification_rules(id) ON DELETE CASCADE,
    INDEX idx_condition_group (condition_group)
);
```

## Core Architecture

### Engine Flow
1. Event occurs in system
2. Engine checks for matching triggers
3. For each matching trigger:
   - Loads associated rule
   - Evaluates conditions
   - If conditions pass, executes actions
4. Actions may include:
   - Sending emails
   - Creating in-app notifications
   - Triggering webhooks

### Key Components
- `NotificationEngine`: Core processing class
- `ConditionEvaluator`: Handles condition logic
- `ActionDispatcher`: Manages action execution
- `TenantValidator`: Ensures tenant isolation

## UI Components

### Management Interface
- Rule creation/editing
- Trigger configuration
- Condition builder
- Action setup

### Views
1. `create.php`: Form for new notifications
2. `list.php`: Notification inbox
3. `management.php`: Rule configuration
4. `preferences.php`: User notification settings
5. `schedule.php`: Scheduled notification form

## Security Considerations
- Tenant isolation enforced at all levels
- CSRF protection on all forms
- Input validation for:
  - Rule conditions
  - Action parameters
  - Template variables

## Usage Examples

### Creating a Rule
```php
// Example from admin/notifications/engine.php
$rule = [
    'tenant_id' => 1,
    'name' => 'New User Welcome',
    'conditions' => json_encode([
        ['field' => 'user.status', 'operator' => '=', 'value' => 'active']
    ]),
    'actions' => json_encode([
        ['type' => 'email', 'template' => 'welcome']
    ])
];
```

### Evaluating Conditions
```php
// Example condition evaluation
$evaluator = new ConditionEvaluator();
$result = $evaluator->evaluate(
    $conditions, 
    ['user' => ['status' => 'active']]
);
```

## Integration Points
- Workflow system
- User management
- Template engine
- Email/SMS gateways