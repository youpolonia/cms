const TenantHeader = {
    template: `
        <header class="analytics-header">
            <h1>Tenant Analytics Dashboard</h1>
            <div class="time-range-selector">
                <select v-model="timeRange" @change="updateTimeRange">
                    <option value="7d">Last 7 Days</option>
                    <option value="30d">Last 30 Days</option>
                    <option value="90d">Last 90 Days</option>
                </select>
            </div>
        </header>
    `,
    data() {
        return {
            timeRange: '30d'
        }
    },
    methods: {
        updateTimeRange() {
            this.$emit('time-range-changed', this.timeRange);
        }
    }
};