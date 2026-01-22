# Changelog - v2.5.0 (2026-01-22)

## 🎯 Major Enhancement: Google AI Overview Detection

### Overview
Enhanced AI citation tracking to detect Google AI Overview and Bing AI traffic using advanced heuristic pattern matching with confidence scoring.

### Related Issues
- Addresses user request: "Track Google AI Overview citations"
- Previous limitation: Google AI Overview clicks were indistinguishable from regular search traffic

---

## ✨ New Features

### 1. Google AI Overview Detection
- **Heuristic Pattern Matching**: Analyzes query patterns to identify AI Overview traffic
- **Confidence Scoring**: 0.4-0.85 confidence range for heuristic detections
- **Detection Patterns**:
  - Informational queries: "how to", "what is", "why does", "when should"
  - Query complexity analysis (word count scoring)
  - Technical keyword detection
  - Conversational structure recognition

**Example Detected Query:**
```
Query: "can you tell me more about monocubed and their portfolio"
Platform: Google AI Overview
Confidence: 0.65
Method: heuristic_http_referer
```

### 2. Bing AI/Copilot Detection
- URL parameter analysis (`&form=MA13FV`)
- Fallback to query pattern heuristics
- Confidence: 0.9 with form parameter, 0.4-0.75 with heuristics

### 3. Additional AI Platforms
- **Kagi**: AI-powered search engine
- **Neeva**: Privacy-focused AI search
- All with direct detection (confidence: 0.95)

### 4. Confidence Scoring System
| Detection Method | Confidence | Use Case |
|-----------------|------------|----------|
| UTM parameters | 1.0 | ChatGPT, definitive detection |
| HTTP referer (direct) | 0.95 | Perplexity, Claude, very high confidence |
| Heuristic (high match) | 0.7-0.85 | Strong AI Overview indicators |
| Heuristic (medium) | 0.5-0.7 | Probable AI Overview |
| Heuristic (low) | 0.4-0.5 | Possible AI Overview, review needed |

### 5. Database & Export Integration
- Added `detection_method` field to database tracking
- Added `confidence_score` field to database tracking
- Both fields included in CSV exports for analysis

---

## 📊 Export Data Analysis

### New CSV Columns
```csv
detection_method,confidence_score,ai_platform,search_query
utm_parameter,1.00,ChatGPT,""
http_referer,0.95,Perplexity,"wordpress optimization"
heuristic_http_referer,0.65,Google AI Overview,"how to optimize wordpress"
heuristic_http_referer,0.42,Google AI Overview,"monocubed"
```

### Pattern Analysis Workflow
1. **Export citations** to CSV
2. **Filter by confidence**:
   - High (>0.8): Likely real AI Overview
   - Medium (0.5-0.8): Probable, worth analyzing
   - Low (<0.5): Review for false positives
3. **Analyze query patterns** to tune heuristics
4. **Adjust weights** in code based on your traffic patterns

---

## 🔧 Technical Changes

### Files Modified
1. **includes/class-ta-ai-citation-tracker.php** (231 additions)
   - Added Google AI Overview and Bing AI to platform list
   - Implemented `apply_heuristics()` method
   - Added `detect_google_ai_overview()` with pattern matching
   - Added `detect_bing_copilot()` with URL parameter check
   - Enhanced `detect_citation_traffic()` with confidence scoring

2. **includes/class-ta-bot-analytics.php** (17 additions)
   - Fixed `track_citation_click()` to pass detection_method
   - Fixed `track_citation_click()` to pass confidence_score
   - Ensures database receives complete citation data

3. **third-audience.php**
   - Version bump: 2.4.0 → 2.5.0

### Database Schema
Already in place (v1.2.0):
- `detection_method` varchar(50)
- `confidence_score` decimal(3,2)

### Git Commits
```
7937b00 Pass detection_method and confidence_score to database
b2dc6b8 Add Google AI Overview detection with heuristic patterns (v2.5.0)
```

---

## 🚀 Previous Versions (Also in Main)

### v2.4.0 - Production-Grade Markdown
- GitHub Flavored Markdown tables
- Code blocks with language hints
- SEO metadata (description, keywords from Yoast/RankMath)
- Enhanced footer with cache status
- Removed duplicate `last_modified` field

**Commit:** `04b5743`

### v2.3.0 - Dynamic Bot Detection
- Machine learning-ready detection pipeline
- Unknown bot classification system
- External bot database sync
- Bot fingerprinting and behavioral analysis

**Commit:** `c99c5cb`

### v2.2.0 - AI Citations Tracking
- ChatGPT, Perplexity, Claude, Gemini detection
- Search query extraction from Perplexity
- UTM parameter detection for ChatGPT
- Dashboard with platform breakdown

**Commit:** `e7754bb`

---

## 📝 Deployment Status

### ✅ GitHub Repository (main branch)
- **Version:** v2.5.0
- **Status:** All features live and pushed
- **URL:** https://github.com/spcaeo/third-audience-wordpress-plugin

### ⚠️ Production Server (www.monocubed.com)
- **Current Version:** v2.2.0 (outdated)
- **Status:** Needs manual update
- **Missing:** v2.3.0, v2.4.0, v2.5.0 features

**To Deploy:**
1. Upload updated plugin files to production
2. Clear WordPress cache to regenerate markdown
3. Verify version in markdown footer

---

## 🎓 How to Use New Features

### 1. Monitor Google AI Overview Traffic
Navigate to **WordPress Admin → Bot Analytics → AI Citations**

You'll now see:
- Google AI Overview citations in platform breakdown
- Confidence scores for each detection
- Search queries extracted from Google referrer

### 2. Export and Analyze
1. Click **"Export CSV"** on AI Citations page
2. Open in Excel/Google Sheets
3. Filter by:
   - `detection_method = "heuristic_http_referer"`
   - `ai_platform = "Google AI Overview"`
4. Sort by `confidence_score` to identify questionable detections

### 3. Tune Heuristics (Advanced)
If you see false positives:
1. Analyze query patterns in low-confidence detections
2. Edit `includes/class-ta-ai-citation-tracker.php:detect_google_ai_overview()`
3. Adjust pattern weights based on your traffic
4. Test and commit changes

---

## 🐛 Known Limitations

### Google AI Overview Detection
- **Not 100% accurate**: Heuristic-based, can't be definitive
- **Max confidence: 0.85**: Pattern matching has inherent uncertainty
- **Query-dependent**: Works best with informational queries
- **False positives possible**: Regular Google searches with similar patterns may be flagged

### Recommendation
Use confidence scores to filter results:
- Trust high confidence (>0.7) for reporting
- Review medium confidence (0.5-0.7) manually
- Investigate low confidence (<0.5) for pattern tuning

---

## 🔮 Future Enhancements

### Potential Improvements
1. **Machine Learning**: Train classifier on confirmed citations
2. **User Feedback Loop**: Allow admins to mark false positives
3. **Google Updates**: Monitor for Google adding citation markers
4. **Pattern Refinement**: Continuously tune heuristics based on analytics

### Community Contribution
If Google adds official AI Overview markers in the future:
- Open GitHub issue with details
- We'll implement direct detection (confidence: 1.0)
- Remove heuristic fallback

---

## 📚 Documentation Updates Needed

### Update These Locations
- [ ] README.md - Add v2.5.0 to feature list
- [ ] GitHub Wiki (if exists) - Document confidence scoring
- [ ] Plugin description - Mention Google AI Overview tracking
- [ ] User guide - Export analysis workflow
- [ ] API docs - New detection_method values

---

## 🙏 Credits

**Developed during Claude Code session 2026-01-22**
- Enhanced AI citation tracking
- Heuristic pattern matching implementation
- Confidence scoring system design
- Database integration fix

**User Request:** "Track Google AI Overview citations like we track ChatGPT"
**Solution:** Advanced heuristic detection with confidence-based filtering
