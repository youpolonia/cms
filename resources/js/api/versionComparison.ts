import axios from 'axios'

export const getVersions = async (contentId: string) => {
  const response = await axios.get(`/api/content/${contentId}/versions/analytics/versions`)
  return response.data
}

export const getSystemStats = async (contentId: string) => {
  const response = await axios.get(`/api/content/${contentId}/versions/analytics/system-stats`)
  return response.data
}

interface VersionComparison {
  diffs: any[];
  line_diffs: any[];
  stats: {
    added: number;
    removed: number;
    unchanged: number;
    added_chars: number;
    removed_chars: number;
    unchanged_chars: number;
    added_words: number;
    removed_words: number;
    unchanged_words: number;
  };
  similarity: number;
  html: string;
  line_html: string;
  version_a: string;
  version_b: string;
  timestamp: string;
  is_autosave_a: boolean;
  is_autosave_b: boolean;
  version_number_a: number;
  version_number_b: number;
  approval_status_a: string;
  approval_status_b: string;
  is_approved_a: boolean;
  is_approved_b: boolean;
  approved_at_a: string | null;
  approved_at_b: string | null;
}

export const getComparison = async (contentId: string, version1: string, version2: string): Promise<VersionComparison> => {
  const response = await axios.post(`/api/content/${contentId}/versions/analytics/compare-versions`, {
    version1,
    version2,
    include_approval: true
  });
  return response.data;
}

export const getFrequentComparisons = async (contentId: string, limit = 5) => {
  const response = await axios.get(`/api/content/${contentId}/versions/analytics/frequent-comparisons`, {
    params: { limit }
  })
  return response.data
}

export const shareComparison = async (contentId: string, version1: string, version2: string) => {
  const response = await axios.post(`/api/content/${contentId}/versions/${version1}/compare/${version2}/share`)
  return response.data
}

export const getSharedComparison = async (token: string) => {
  const response = await axios.get(`/api/shared-comparisons/${token}`)
  return response.data
}
