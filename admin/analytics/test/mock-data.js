// Mock data for analytics dashboard testing
export const mockAnalyticsData = {
    // Basic scenario
    basic: {
        current: {
            engagement: 42,
            content: 18,
            pageViews: 156,
            avgTime: 2.5
        },
        previous: {
            engagement: 38,
            content: 15,
            pageViews: 142,
            avgTime: 2.2
        }
    },

    // High engagement scenario
    highEngagement: {
        current: {
            engagement: 85,
            content: 22,
            pageViews: 320,
            avgTime: 4.1
        },
        previous: {
            engagement: 72,
            content: 20,
            pageViews: 285,
            avgTime: 3.8
        }
    },

    // Empty data scenario
    empty: {
        current: {
            engagement: 0,
            content: 0,
            pageViews: 0,
            avgTime: 0
        },
        previous: {
            engagement: 0,
            content: 0,
            pageViews: 0,
            avgTime: 0
        }
    }
};