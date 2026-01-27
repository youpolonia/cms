/**
 * RecommendationCard - Individual recommendation item display
 */
class RecommendationCard {
  constructor(data) {
    this.data = data;
  }

  render() {
    return `
      <article class="recommendation-card" aria-labelledby="rec-title-${this.data.id}">
        <h3 id="rec-title-${this.data.id}">${this.data.title}</h3>
        ${this.data.image ? `<img src="${this.data.image}" alt="${this.data.title}" loading="lazy">` : ''}
        <p>${this.data.description}</p>
        <div class="feedback-container">
          ${new FeedbackWidget(this.data.id).render()}
        </div>
      </article>
    `;
  }
}