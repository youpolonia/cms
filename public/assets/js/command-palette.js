/**
 * Command Palette - Cmd+K / Ctrl+K
 * Quick navigation and actions in admin panel
 */
class CommandPalette {
    constructor() {
        this.isOpen = false;
        this.commands = [];
        this.filteredCommands = [];
        this.selectedIndex = 0;
        this.mode = "default";
        this.history = new CommandHistory();
        this.search = new FuzzySearch();
        this.init();
    }

    init() {
        this.registerDefaultCommands();
        this.createDOM();
        this.bindEvents();
    }

    registerDefaultCommands() {
        // Navigation
        this.register({ id: "nav-dashboard", title: "Go to Dashboard", icon: "📊", category: "Navigation", keywords: ["home", "main"], action: () => this.navigate("/admin") });
        this.register({ id: "nav-pages", title: "Go to Pages", icon: "📄", category: "Navigation", keywords: ["content", "list"], action: () => this.navigate("/admin/pages") });
        this.register({ id: "nav-articles", title: "Go to Articles", icon: "📰", category: "Navigation", keywords: ["blog", "posts"], action: () => this.navigate("/admin/articles") });
        this.register({ id: "nav-media", title: "Go to Media Library", icon: "🖼️", category: "Navigation", keywords: ["images", "files"], action: () => this.navigate("/admin/media") });
        this.register({ id: "nav-settings", title: "Go to Settings", icon: "⚙️", category: "Navigation", keywords: ["config", "options"], action: () => this.navigate("/admin/settings") });
        this.register({ id: "nav-users", title: "Go to Users", icon: "👥", category: "Navigation", keywords: ["accounts", "admin"], action: () => this.navigate("/admin/users") });
        this.register({ id: "nav-menus", title: "Go to Menus", icon: "☰", category: "Navigation", keywords: ["navigation", "links"], action: () => this.navigate("/admin/menus") });
        this.register({ id: "nav-plugins", title: "Go to Plugins", icon: "🧩", category: "Navigation", keywords: ["extensions", "modules"], action: () => this.navigate("/admin/plugins") });

        // Theme Builder
        this.register({ id: "nav-jtb-templates", title: "JTB Templates", icon: "🎨", category: "Theme Builder", keywords: ["design", "layout"], action: () => this.navigate("/admin/jtb/templates") });
        this.register({ id: "nav-jtb-global", title: "JTB Global Modules", icon: "📦", category: "Theme Builder", keywords: ["reusable", "components"], action: () => this.navigate("/admin/jtb/global-modules") });
        this.register({ id: "nav-jtb-settings", title: "JTB Theme Settings", icon: "🎛️", category: "Theme Builder", keywords: ["styles", "colors"], action: () => this.navigate("/admin/jtb/theme-settings") });

        // AI Tools
        this.register({ id: "nav-ai-tb-v5", title: "AI Theme Builder v5", icon: "🚀", category: "AI Tools", keywords: ["generator", "design"], action: () => this.navigate("/admin/ai-theme-builder-v5") });
        this.register({ id: "nav-ai-copywriter", title: "AI Copywriter", icon: "✍️", category: "AI Tools", keywords: ["content", "generate"], action: () => this.navigate("/admin/ai-copywriter.php") });
        this.register({ id: "nav-ai-seo", title: "AI SEO Assistant", icon: "📈", category: "AI Tools", keywords: ["optimize", "meta"], action: () => this.navigate("/admin/ai-seo-assistant.php") });

        // Actions
        this.register({ id: "action-new-page", title: "Create New Page", icon: "➕", category: "Actions", keywords: ["add", "create"], action: () => this.navigate("/admin/pages/create") });
        this.register({ id: "action-new-article", title: "Create New Article", icon: "✏️", category: "Actions", keywords: ["add", "write"], action: () => this.navigate("/admin/articles/create") });
        this.register({ id: "action-clear-cache", title: "Clear Cache", icon: "🧹", category: "Actions", keywords: ["refresh", "reset"], action: () => this.clearCache() });
        this.register({ id: "action-logout", title: "Logout", icon: "🚪", category: "Actions", keywords: ["exit", "signout"], action: () => this.navigate("/admin/logout") });

        // Search modes
        this.register({ id: "search-pages", title: "Search Pages...", icon: "🔍", category: "Search", keywords: ["find"], action: () => this.setMode("search", "pages") });
        this.register({ id: "search-articles", title: "Search Articles...", icon: "🔎", category: "Search", keywords: ["find"], action: () => this.setMode("search", "articles") });
    }

    register(command) {
        this.commands.push({
            ...command,
            searchString: [command.title, command.category, ...(command.keywords || [])].join(" ").toLowerCase()
        });
    }

    createDOM() {
        const modal = document.createElement("div");
        modal.id = "command-palette";
        modal.innerHTML = `
            <div class="cp-overlay"></div>
            <div class="cp-modal">
                <div class="cp-input-wrapper">
                    <span class="cp-icon"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg></span>
                    <input type="text" id="cp-input" placeholder="Type a command or search..." autocomplete="off"/>
                    <kbd class="cp-shortcut">ESC</kbd>
                </div>
                <div class="cp-results" id="cp-results"></div>
                <div class="cp-footer">
                    <span><kbd>↑↓</kbd> Navigate</span>
                    <span><kbd>↵</kbd> Select</span>
                    <span><kbd>ESC</kbd> Close</span>
                </div>
            </div>
        `;
        document.body.appendChild(modal);
        this.modal = modal;
        this.input = document.getElementById("cp-input");
        this.results = document.getElementById("cp-results");
    }

    bindEvents() {
        document.addEventListener("keydown", (e) => {
            if ((e.metaKey || e.ctrlKey) && e.key === "k") {
                e.preventDefault();
                this.toggle();
            }
        });

        this.modal.querySelector(".cp-overlay").addEventListener("click", () => this.close());

        this.input.addEventListener("input", () => this.filter(this.input.value));

        this.input.addEventListener("keydown", (e) => {
            switch (e.key) {
                case "ArrowDown": e.preventDefault(); this.selectNext(); break;
                case "ArrowUp": e.preventDefault(); this.selectPrev(); break;
                case "Enter": e.preventDefault(); this.executeSelected(); break;
                case "Escape": this.close(); break;
            }
        });
    }

    toggle() { this.isOpen ? this.close() : this.open(); }

    open() {
        this.modal.classList.add("open");
        this.isOpen = true;
        this.input.value = "";
        this.input.focus();
        this.showInitialResults();
    }

    close() {
        this.modal.classList.remove("open");
        this.isOpen = false;
        this.selectedIndex = 0;
        this.mode = "default";
    }

    showInitialResults() {
        const recent = this.history.getRecent(3);
        const recentCommands = recent.map(id => this.commands.find(c => c.id === id)).filter(Boolean);
        this.filteredCommands = [...recentCommands, ...this.commands.filter(c => !recent.includes(c.id))];
        this.render(recentCommands.length > 0 ? "Recent" : null);
    }

    filter(query) {
        if (!query) { this.showInitialResults(); return; }
        if (query.startsWith(">")) { this.filterByCategory("Actions", query.slice(1).trim()); return; }
        if (query.startsWith("/")) { this.filterByCategory("Navigation", query.slice(1).trim()); return; }
        this.filteredCommands = this.search.filter(this.commands, query);
        this.selectedIndex = 0;
        this.render();
    }

    filterByCategory(category, query) {
        this.filteredCommands = this.commands
            .filter(c => c.category === category)
            .filter(c => !query || this.search.match(c.searchString, query));
        this.selectedIndex = 0;
        this.render(category);
    }

    render(sectionTitle = null) {
        if (this.filteredCommands.length === 0) {
            this.results.innerHTML = `<div class="cp-empty"><span>No results found</span></div>`;
            return;
        }

        let html = "";
        let currentCategory = null;

        this.filteredCommands.forEach((cmd, index) => {
            const category = sectionTitle || cmd.category;
            if (category !== currentCategory) {
                currentCategory = category;
                html += `<div class="cp-category">${category}</div>`;
            }
            html += `<div class="cp-item ${index === this.selectedIndex ? "selected" : ""}" data-index="${index}">
                <span class="cp-item-icon">${cmd.icon}</span>
                <span class="cp-item-title">${cmd.title}</span>
                ${cmd.shortcut ? `<kbd class="cp-item-shortcut">${cmd.shortcut}</kbd>` : ""}
            </div>`;
        });

        this.results.innerHTML = html;

        this.results.querySelectorAll(".cp-item").forEach(item => {
            item.addEventListener("click", () => { this.selectedIndex = parseInt(item.dataset.index); this.executeSelected(); });
            item.addEventListener("mouseenter", () => { this.selectedIndex = parseInt(item.dataset.index); this.updateSelection(); });
        });
    }

    selectNext() { if (this.selectedIndex < this.filteredCommands.length - 1) { this.selectedIndex++; this.updateSelection(); } }
    selectPrev() { if (this.selectedIndex > 0) { this.selectedIndex--; this.updateSelection(); } }

    updateSelection() {
        this.results.querySelectorAll(".cp-item").forEach((item, index) => item.classList.toggle("selected", index === this.selectedIndex));
        const selected = this.results.querySelector(".cp-item.selected");
        if (selected) selected.scrollIntoView({ block: "nearest" });
    }

    executeSelected() {
        const command = this.filteredCommands[this.selectedIndex];
        if (command) { this.history.add(command.id); this.close(); command.action(); }
    }

    navigate(url) { window.location.href = url; }

    async clearCache() {
        try {
            const response = await fetch("/api/clear-cache", { method: "POST", headers: { "X-Requested-With": "XMLHttpRequest" } });
            if (response.ok) this.showToast("Cache cleared successfully");
            else this.showToast("Failed to clear cache", "error");
        } catch (e) { this.showToast("Failed to clear cache", "error"); }
    }

    setMode(mode, type) {
        this.mode = mode;
        this.input.placeholder = `Search ${type}...`;
        this.input.value = "";
        this.input.focus();
        this.loadDynamicResults(type);
    }

    async loadDynamicResults(type) {
        try {
            const response = await fetch(`/api/search?type=${type}&limit=20`);
            const data = await response.json();
            if (data.success) {
                this.filteredCommands = data.data.map(item => ({
                    id: `${type}-${item.id}`,
                    title: item.title,
                    icon: type === "pages" ? "📄" : "📰",
                    category: type.charAt(0).toUpperCase() + type.slice(1),
                    action: () => this.navigate(item.url || `/admin/${type}/edit/${item.id}`)
                }));
                this.render();
            }
        } catch (e) { console.error("Failed to load results:", e); }
    }

    showToast(message, type = "success") {
        if (window.showNotification) window.showNotification(message, type);
        else if (window.Toastify) Toastify({ text: message, duration: 3000, gravity: "top", position: "right", style: { background: type === "error" ? "#ef4444" : "#22c55e" } }).showToast();
        else alert(message);
    }
}

class FuzzySearch {
    filter(items, query) {
        const q = query.toLowerCase();
        return items.map(item => ({ item, score: this.score(item.searchString, q) }))
            .filter(x => x.score > 0)
            .sort((a, b) => b.score - a.score)
            .map(x => x.item);
    }

    match(text, query) { return this.score(text.toLowerCase(), query.toLowerCase()) > 0; }

    score(text, query) {
        if (!query) return 1;
        let score = 0, queryIndex = 0, consecutive = 0;
        for (let i = 0; i < text.length && queryIndex < query.length; i++) {
            if (text[i] === query[queryIndex]) {
                score += 1 + consecutive * 2;
                consecutive++;
                queryIndex++;
                if (i === 0 || text[i - 1] === " ") score += 5;
            } else consecutive = 0;
        }
        return queryIndex === query.length ? score : 0;
    }
}

class CommandHistory {
    constructor() { this.key = "cp_history"; this.maxItems = 10; }

    getRecent(limit = 5) {
        try { return JSON.parse(localStorage.getItem(this.key) || "[]").slice(0, limit); }
        catch { return []; }
    }

    add(commandId) {
        try {
            let history = JSON.parse(localStorage.getItem(this.key) || "[]");
            history = history.filter(id => id !== commandId);
            history.unshift(commandId);
            history = history.slice(0, this.maxItems);
            localStorage.setItem(this.key, JSON.stringify(history));
        } catch (e) { console.error("Failed to save history:", e); }
    }
}

document.addEventListener("DOMContentLoaded", () => { window.commandPalette = new CommandPalette(); });
