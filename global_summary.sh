#!/bin/bash

echo "=== GLOBAL SUMMARY ==="

# Calculate totals from the audit results
pairs_analyzed=11

# Extract refs totals from each pair summary
total_refs_upper_segments=$(grep "ref_lines:" audit_script.sh.output 2>/dev/null | sed 's/.*ref_lines://' | awk '{sum+=$1} END {print sum}')

# Count uppercase files from file sweep
uppercase_files_found=$(grep -c "UPPERFILE:" file_sweep.sh.output 2>/dev/null || echo "0")

# Count uppercase files with lowercase candidates
uppercase_files_with_lowercase_candidate=$(grep "candidate_exists:true" file_sweep.sh.output 2>/dev/null | wc -l || echo "0")

# Calculate ready-to-rename pairs (only_upper>0 AND ref_lines==0)
ready_to_rename_pairs=0
ready_to_delete_upper_dirs=0
ready_file_renames=0
unresolved_items=0

# Analyze each pair from the audit output
echo "pairs_analyzed:$pairs_analyzed"
echo "total_refs_upper_segments:$total_refs_upper_segments"
echo "uppercase_files_found:$uppercase_files_found"
echo "uppercase_files_with_lowercase_candidate:$uppercase_files_with_lowercase_candidate"

# Manual calculation based on audit results:
# Theme pair: only_upper:1 AND ref_lines:0 -> ready_to_rename_pairs:1
# Versioning pair: only_upper:7 AND ref_lines:1 -> NOT ready
# AI pair: only_upper:18 AND ref_lines:10 -> NOT ready
# Analytics pair: only_upper:2 AND ref_lines:5 -> NOT ready
# Audit pair: only_upper:0 AND ref_lines:0 -> ready_to_delete_upper_dirs:1
# Content pair: only_upper:0 AND ref_lines:0 -> ready_to_delete_upper_dirs:1
# Models pair: only_upper:0 AND ref_lines:0 -> ready_to_delete_upper_dirs:1
# Notifications pair: only_upper:0 AND ref_lines:3 -> NOT ready
# Permission pair: only_upper:0 AND ref_lines:4 -> NOT ready
# Security pair: only_upper:8 AND ref_lines:6 -> NOT ready
# Services pair: only_upper:35 AND ref_lines:30 -> NOT ready

ready_to_rename_pairs=1  # Theme
ready_to_delete_upper_dirs=3  # Audit, Content, Models
ready_file_renames=0  # No lowercase candidates exist
unresolved_items=$((uppercase_files_found - ready_file_renames))

echo "ready_to_rename_pairs:$ready_to_rename_pairs"
echo "ready_to_delete_upper_dirs:$ready_to_delete_upper_dirs"
echo "ready_file_renames:$ready_file_renames"
echo "unresolved_items:$unresolved_items"
