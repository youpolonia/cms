import VersionComparator from '../../../includes/VersionComparator';

class VersionComparisonView {
    constructor(containerId, version1, version2) {
        this.container = document.getElementById(containerId);
        this.version1 = version1;
        this.version2 = version2;
        this.comparator = new VersionComparator();
    }

    render() {
        const diff = this.comparator.compare(this.version1, this.version2);
        this.container.innerHTML = `<pre>${diff}</pre>`;
    }
}

export default VersionComparisonView;