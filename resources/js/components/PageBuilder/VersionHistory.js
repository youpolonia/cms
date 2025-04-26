export default class VersionHistory {
  constructor() {
    this.versions = [];
    this.currentIndex = -1;
    this.maxVersions = 50;
  }

  addVersion(content, metadata = {}) {
    // Don't save if identical to current version
    if (this.versions.length > 0 && 
        JSON.stringify(content) === JSON.stringify(this.getCurrentVersion().content)) {
      return;
    }

    // Truncate future versions if we're not at the end
    if (this.currentIndex < this.versions.length - 1) {
      this.versions = this.versions.slice(0, this.currentIndex + 1);
    }

    const version = {
      content: JSON.parse(JSON.stringify(content)),
      timestamp: new Date(),
      metadata: {
        action: metadata.action || 'manual',
        user: metadata.user || 'system',
        ...metadata
      }
    };

    this.versions.push(version);
    this.currentIndex = this.versions.length - 1;

    // Enforce max versions
    if (this.versions.length > this.maxVersions) {
      this.versions.shift();
      this.currentIndex--;
    }
  }

  getCurrentVersion() {
    return this.versions[this.currentIndex];
  }

  undo() {
    if (this.currentIndex > 0) {
      this.currentIndex--;
      return this.getCurrentVersion();
    }
    return null;
  }

  redo() {
    if (this.currentIndex < this.versions.length - 1) {
      this.currentIndex++;
      return this.getCurrentVersion();
    }
    return null;
  }

  getVersion(index) {
    return this.versions[index];
  }

  getVersionDiff(index1, index2) {
    const v1 = this.getVersion(index1);
    const v2 = this.getVersion(index2);
    
    if (!v1 || !v2) return null;

    return this.deepDiff(v1.content, v2.content);
  }

  deepDiff(obj1, obj2) {
    const diff = {};
    
    // Compare properties of obj1 against obj2
    for (const key in obj1) {
      if (typeof obj1[key] === 'object' && obj1[key] !== null) {
        const nestedDiff = this.deepDiff(obj1[key], obj2[key]);
        if (Object.keys(nestedDiff).length > 0) {
          diff[key] = nestedDiff;
        }
      } else if (!obj2.hasOwnProperty(key) || obj1[key] !== obj2[key]) {
        diff[key] = {
          old: obj1[key],
          new: obj2[key]
        };
      }
    }

    // Check for new properties in obj2
    for (const key in obj2) {
      if (!obj1.hasOwnProperty(key)) {
        diff[key] = {
          old: undefined,
          new: obj2[key]
        };
      }
    }

    return diff;
  }

  exportHistory() {
    return {
      versions: this.versions,
      currentIndex: this.currentIndex
    };
  }

  importHistory(data) {
    this.versions = data.versions;
    this.currentIndex = data.currentIndex;
  }
}