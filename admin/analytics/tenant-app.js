const TenantAnalyticsApp = {
    components: {
        'tenant-header': TenantHeader,
        'tenant-usage-chart': TenantUsageChart,
        'resource-chart': ResourceChart
    },
    data() {
        return {
            tenantData: {},
            loading: true
        }
    },
    mounted() {
        this.fetchTenantData();
    },
    methods: {
        async fetchTenantData() {
            try {
                const response = await fetch('/api/tenant-analytics');
                this.tenantData = await response.json();
                this.loading = false;
            } catch (error) {
                console.error('Error fetching tenant data:', error);
            }
        }
    }
};

const app = Vue.createApp(TenantAnalyticsApp);
app.mount('#tenant-analytics-app');