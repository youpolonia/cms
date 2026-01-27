const HeaderComponent = {
    template: `
        <header class="dashboard-header">
            <h1>Analytics Dashboard</h1>
            <div class="header-controls">
                <div class="date-range-selector">
                    <select v-model="selectedRange" @change="updateRange">
                        <option value="7d">Last 7 Days</option>
                        <option value="30d">Last 30 Days</option>
                        <option value="90d">Last 90 Days</option>
                        <option value="custom">Custom Range</option>
                    </select>
                </div>
                <div class="export-buttons">
                    <button @click="exportData('csv')">Export CSV</button>
                    <button @click="exportData('json')">Export JSON</button>
                </div>
            </div>
        </header>
    `,
    data() {
        return {
            selectedRange: '7d'
        }
    },
    methods: {
        updateRange() {
            this.$emit('range-updated', this.selectedRange);
        },
        exportData(format) {
            this.$emit('export-requested', format);
        }
    }
};

app.component('analytics-header', HeaderComponent);