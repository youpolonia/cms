/**
 * RecommendationList - Main container for displaying recommendations
 */
class RecommendationList {
  constructor(containerId, apiEndpoint) {
    this.container = document.getElementById(containerId);
    this.apiEndpoint = apiEndpoint;
    this.recommendations = [];
    this.error = null;
    this.loading = false;
  }

  async fetchRecommendations() {
    this.setLoading(true);
    try {
      const response = await fetch(this.apiEndpoint);
      if (!response.ok) throw new Error('Failed to fetch recommendations');
      this.recommendations = await response.json();
      this.error = null;
      this.render();
    } catch (err) {
      this.error = err.message;
      this.renderError();
    } finally {
      this.setLoading(false);
    }
  }

  setLoading(isLoading) {
    this.loading = isLoading;
    this.render();
  }

  render() {
    if (this.loading) {
      this.container.innerHTML = '<div class="loading">Loading recommendations...</div>';
      return;
    }

    if (this.error) {
      this.renderError();
      return;
    }

    let html = '<div class="recommendation-list">';
    this.recommendations.forEach(rec => {
      const card = new RecommendationCard(rec);
      html += card.render();
    });
    html += '</div>';
    this.container.innerHTML = html;
  }

  renderError() {
    this.container.innerHTML = `
      <div class="error">
        <p>Failed to load recommendations: ${this.error}</p>
        <button onclick="window.location.reload()">Retry</button>
      </div>
    `;
  }
}