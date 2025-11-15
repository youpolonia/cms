# Error Monitoring Setup

## Sentry Integration

### Frontend Configuration
1. Added `@sentry/vue` package
2. Configured in `resources/js/app.js`:
   ```javascript
   import * as Sentry from '@sentry/vue';
   import { BrowserTracing } from '@sentry/tracing';

   if (import.meta.env.VITE_SENTRY_DSN) {
       Sentry.init({
           dsn: import.meta.env.VITE_SENTRY_DSN,
           integrations: [new BrowserTracing()],
           environment: import.meta.env.VITE_SENTRY_ENVIRONMENT,
           tracesSampleRate: 1.0,
       });
   }
   ```

### Backend Configuration
1. Integrated Sentry SDK directly
2. Custom configuration in `config/monitoring.php`
3. Environment variables:
   ```
   SENTRY_DSN=
   SENTRY_LARAVEL_DSN=
   SENTRY_ENVIRONMENT=production
   SENTRY_SAMPLE_RATE=1.0
   SENTRY_TRACES_SAMPLE_RATE=0.2
   SENTRY_PROFILES_SAMPLE_RATE=0.1
   ```

## Alert Thresholds
- Critical errors: Immediate notification
- High priority errors: Notify within 1 hour
- Medium priority errors: Daily digest
- Low priority errors: Weekly report

## Automated Reporting
1. Daily error summary sent to engineering team
2. Weekly performance report
3. Critical error alerts via Slack/email

## Monitoring Procedures
1. Check Sentry dashboard daily
2. Review weekly reports
3. Prioritize fixes based on error frequency and impact
4. Document resolutions in Sentry issues