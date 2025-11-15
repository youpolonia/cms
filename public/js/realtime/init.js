document.addEventListener('DOMContentLoaded', () => {
    const documentId = document.body.dataset.documentId;
    const userId = document.body.dataset.userId;
    
    if (documentId && userId) {
        window.presenceManager = new PresenceManager(documentId, userId);
        presenceManager.connect();
        
        window.addEventListener('beforeunload', () => {
            presenceManager.disconnect();
        });
    }
});