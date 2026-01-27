/**
 * Theme Builder 3.0 - AI Generate JavaScript
 * FIXED: Added prompt() dialog for user input
 */

Object.assign(TB, {
    async handleAIGenerate(type, field, sIdx, rIdx, cIdx, mIdx, button) {
        const mod = this.content.sections[sIdx]?.rows[rIdx]?.columns[cIdx]?.modules[mIdx];
        if (!mod) return;

        const defaultContexts = {
            heading: "a compelling website section",
            text: "engaging website content",
            button: "a call-to-action button",
            cta_title: "getting users to take action",
            cta_subtitle: "supporting the main call-to-action",
            cta_button: "encouraging sign-ups or purchases",
            hero_title: "a hero section headline",
            hero_subtitle: "supporting hero text",
            hero_button: "a hero section call-to-action button",
            testimonial: "customer satisfaction with our service",
            pricing_features: "features included in this plan",
            quote: "inspiration and motivation",
            blurb_title: "a service or feature highlight",
            blurb_text: "describing a key benefit",
            toggle_title: "an expandable content section",
            toggle_content: "hidden content that expands",
            counter_title: "a statistic or metric",
            bar_counters: "a skill or progress bar"
        };

        const context = prompt("What should this " + type.replace(/_/g, " ") + " be about?", defaultContexts[type] || "your topic");
        if (!context) return;

        const originalText = button.innerHTML;
        button.innerHTML = "⏳ Generating...";
        button.disabled = true;

        try {
            const response = await fetch("/admin/theme-builder/ai/generate", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                credentials: "same-origin",
                body: JSON.stringify({
                    type: type,
                    context: context,
                    page_title: document.getElementById("templateName")?.value || "",
                    csrf_token: this.csrfToken || ""
                })
            });

            const data = await response.json();

            if (data.success && data.content) {
                if (!mod.content) mod.content = {};
                
                if (type === "pricing_features" && Array.isArray(data.content)) {
                    mod.content[field] = data.content;
                } else {
                    mod.content[field] = data.content;
                }

                this.isDirty = true;
                if (this.saveToHistory) this.saveToHistory();
                if (this.renderCanvas) this.renderCanvas();
                this.selectModule(sIdx, rIdx, cIdx, mIdx);
                if (this.toast) this.toast("Content generated!", "success");
            } else {
                const errMsg = data.error || "Generation failed";
                if (this.toast) this.toast(errMsg, "error");
            }
        } catch (err) {
            const errMsg = "Error: " + err.message;
            if (this.toast) this.toast(errMsg, "error");
        } finally {
            button.innerHTML = originalText;
            button.disabled = false;
        }
    }
});