import React, { useEffect, useRef, useState } from 'react';
import { Chart, registerables } from 'chart.js';

// Register all Chart.js components
Chart.register(...registerables);

// Cache configuration
const CACHE_TTL = 300000; // 5 minutes in ms
const MAX_RETRIES = 3;
const RETRY_DELAY = 1000; // 1 second

/**
 * SuggestionDashboard - Enhanced analytics dashboard with optimizations
 * Features:
 * - Robust error handling with retry logic
 * - Client-side caching with TTL
 * - Pagination support
 * - Enhanced loading indicators
 */
const SuggestionDashboard = () => {
  const [data, setData] = useState(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);
  const [page, setPage] = useState(1);
  const [totalPages, setTotalPages] = useState(1);
  const chartRef = useRef(null);
  const chartInstance = useRef(null);
  const refreshInterval = useRef(null);
  const cache = useRef({
    data: null,
    timestamp: 0
  });

  // API endpoint for fetching suggestion metrics
  const API_ENDPOINT = '/api/analytics/suggestions';

  // Enhanced fetch with retry logic
  const fetchWithRetry = async (url, options = {}, retries = MAX_RETRIES) => {
    try {
      const response = await fetch(url, options);
      
      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
      }
      
      return await response.json();
    } catch (err) {
      if (retries > 0) {
        await new Promise(resolve => setTimeout(resolve, RETRY_DELAY));
        return fetchWithRetry(url, options, retries - 1);
      }
      throw err;
    }
  };

  // Fetch data from API with caching
  const fetchData = async () => {
    try {
      setLoading(true);
      
      // Check cache first
      const now = Date.now();
      if (cache.current.data && now - cache.current.timestamp < CACHE_TTL) {
        setData(cache.current.data);
        setError(null);
        return;
      }

      const result = await fetchWithRetry(`${API_ENDPOINT}?page=${page}`);
      
      // Update cache
      cache.current = {
        data: result,
        timestamp: now
      };

      setData(result.data);
      setTotalPages(result.meta?.totalPages || 1);
      setError(null);
    } catch (err) {
      setError(err.message);
      console.error('Error fetching suggestion metrics:', err);
    } finally {
      setLoading(false);
    }
  };

  // Initialize chart
  const initChart = () => {
    if (!data || !chartRef.current) return;

    const ctx = chartRef.current.getContext('2d');

    // Destroy previous chart instance if exists
    if (chartInstance.current) {
      chartInstance.current.destroy();
    }

    chartInstance.current = new Chart(ctx, {
      type: 'bar',
      data: {
        labels: data.labels || [],
        datasets: [
          {
            label: 'Suggestions',
            data: data.values || [],
            backgroundColor: 'rgba(54, 162, 235, 0.5)',
            borderColor: 'rgba(54, 162, 235, 1)',
            borderWidth: 1,
          },
        ],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            position: 'top',
          },
          title: {
            display: true,
            text: 'Suggestion Analytics',
          },
        },
        scales: {
          y: {
            beginAtZero: true,
          },
        },
      },
    });
  };

  // Set up real-time updates
  const setupRealTimeUpdates = () => {
    // Clear existing interval
    if (refreshInterval.current) {
      clearInterval(refreshInterval.current);
    }

    // Set new interval (every 30 seconds)
    refreshInterval.current = setInterval(() => {
      fetchData();
    }, 30000);
  };

  // Handle page change
  const handlePageChange = (newPage) => {
    if (newPage >= 1 && newPage <= totalPages) {
      setPage(newPage);
    }
  };

  // Initial setup
  useEffect(() => {
    fetchData();
    setupRealTimeUpdates();

    return () => {
      // Clean up on unmount
      if (refreshInterval.current) {
        clearInterval(refreshInterval.current);
      }
      if (chartInstance.current) {
        chartInstance.current.destroy();
      }
    };
  }, []);

  // Update chart when data changes
  useEffect(() => {
    initChart();
  }, [data]);

  // Fetch new page when page changes
  useEffect(() => {
    fetchData();
  }, [page]);

  return (
    <div className="suggestion-dashboard" style={{
      position: 'relative',
      height: '400px',
      width: '100%',
      maxWidth: '800px',
      margin: '0 auto',
    }}>
      {loading && !error && (
        <div className="loading-indicator" style={{
          position: 'absolute',
          top: '50%',
          left: '50%',
          transform: 'translate(-50%, -50%)',
          padding: '20px',
          background: 'rgba(255,255,255,0.9)',
          borderRadius: '8px',
          boxShadow: '0 2px 10px rgba(0,0,0,0.1)'
        }}>
          <div style={{ textAlign: 'center' }}>
            <div className="spinner" style={{
              border: '4px solid rgba(0,0,0,0.1)',
              width: '36px',
              height: '36px',
              borderRadius: '50%',
              borderLeftColor: '#09f',
              animation: 'spin 1s linear infinite',
              margin: '0 auto 10px'
            }} />
            Loading suggestion data...
          </div>
        </div>
      )}
      
      {error && (
        <div className="error-message" style={{ 
          color: 'red',
          padding: '15px',
          background: '#ffeeee',
          borderRadius: '4px',
          marginBottom: '15px'
        }}>
          Error: {error}
          <button 
            onClick={fetchData}
            style={{
              marginLeft: '10px',
              padding: '5px 10px',
              background: '#ff4444',
              color: 'white',
              border: 'none',
              borderRadius: '4px',
              cursor: 'pointer'
            }}
          >
            Retry
          </button>
        </div>
      )}

      {!loading && !error && data && (
        <>
          <canvas ref={chartRef} />
          {totalPages > 1 && (
            <div style={{
              display: 'flex',
              justifyContent: 'center',
              marginTop: '20px',
              gap: '10px'
            }}>
              <button 
                onClick={() => handlePageChange(page - 1)}
                disabled={page === 1}
                style={{
                  padding: '5px 10px',
                  background: page === 1 ? '#ccc' : '#09f',
                  color: 'white',
                  border: 'none',
                  borderRadius: '4px',
                  cursor: page === 1 ? 'not-allowed' : 'pointer'
                }}
              >
                Previous
              </button>
              <span style={{ lineHeight: '28px' }}>
                Page {page} of {totalPages}
              </span>
              <button 
                onClick={() => handlePageChange(page + 1)}
                disabled={page === totalPages}
                style={{
                  padding: '5px 10px',
                  background: page === totalPages ? '#ccc' : '#09f',
                  color: 'white',
                  border: 'none',
                  borderRadius: '4px',
                  cursor: page === totalPages ? 'not-allowed' : 'pointer'
                }}
              >
                Next
              </button>
            </div>
          )}
        </>
      )}
    </div>
  );
};

export default SuggestionDashboard;