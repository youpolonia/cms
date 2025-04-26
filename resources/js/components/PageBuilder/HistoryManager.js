export default class HistoryManager {
    constructor() {
        this.history = [];
        this.currentIndex = -1;
        this.maxHistory = 50;
    }

    addState(state) {
        // Remove any states after current index (if we're not at the end)
        if (this.currentIndex < this.history.length - 1) {
            this.history = this.history.slice(0, this.currentIndex + 1);
        }

        // Add new state
        this.history.push(JSON.parse(JSON.stringify(state)));
        this.currentIndex++;

        // Limit history size
        if (this.history.length > this.maxHistory) {
            this.history.shift();
            this.currentIndex--;
        }
    }

    canUndo() {
        return this.currentIndex > 0;
    }

    canRedo() {
        return this.currentIndex < this.history.length - 1;
    }

    undo() {
        if (!this.canUndo()) return null;
        this.currentIndex--;
        return this.history[this.currentIndex];
    }

    redo() {
        if (!this.canRedo()) return null;
        this.currentIndex++;
        return this.history[this.currentIndex];
    }

    getCurrentState() {
        return this.history[this.currentIndex];
    }

    clear() {
        this.history = [];
        this.currentIndex = -1;
    }
}