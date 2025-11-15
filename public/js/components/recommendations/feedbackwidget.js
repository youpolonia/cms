/**
 * FeedbackWidget - Handles user ratings/feedback for recommendations
 */
class FeedbackWidget {
  constructor(recommendationId) {
    this.recommendationId = recommendationId;
    this.rating = 0;
    this.submitted = false;
  }

  render() {
    if (this.submitted) {
      return `<p class="feedback-thanks">Thanks for your feedback!</p>`;
    }

    return `
      <fieldset class="feedback-widget" aria-labelledby="feedback-label-${this.recommendationId}">
        <legend id="feedback-label-${this.recommendationId}">Rate this recommendation</legend>
        <div class="rating-stars">
          ${[1, 2, 3, 4, 5].map(star => `
            <button 
              type="button" 
              class="star ${this.rating >= star ? 'active' : ''}" 
              aria-label="${star} star${star !== 1 ? 's' : ''}"
              onclick="document.querySelectorAll('.feedback-widget button.star').forEach((el, i) => {
                el.classList.toggle('active', i < ${star});
                this.rating = ${star};
              })"
            >
              ★
            </button>
          `).join('')}
        </div>
        <button 
          type="button" 
          class="submit-feedback" 
          onclick="this.submitFeedback()"
          ${this.rating === 0 ? 'disabled' : ''}
        >
          Submit Rating
        </button>
      </fieldset>
    `;
  }

  async submitFeedback() {
    try {
      const response = await fetch('/api/recommendations/feedback', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          recommendationId: this.recommendationId,
          rating: this.rating
        })
      });

      if (response.ok) {
        this.submitted = true;
        this.render();
      }
    } catch (err) {
      console.error('Failed to submit feedback:', err);
    }
  }
}