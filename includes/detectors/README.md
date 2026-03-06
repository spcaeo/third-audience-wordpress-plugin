# Bot Detectors

This directory contains bot detection implementations for the Third Audience plugin.

## Heuristic Detector

**File**: `class-ta-heuristic-detector.php`

The Heuristic Detector uses pattern matching to automatically detect bots from user agent strings.

### Detection Indicators

The detector analyzes the following patterns:

1. **Compatible Pattern** (High confidence: +0.4)
   - Pattern: `(compatible; BotName/version)`
   - Example: `Mozilla/5.0 (compatible; Googlebot/2.1; +http://...)`

2. **Documentation URL** (High confidence: +0.4)
   - Pattern: `+http://` or `+https://`
   - Example: `MyBot/1.0 (+https://example.com/bot)`

3. **Version Pattern** (Medium confidence: +0.3)
   - Pattern: `BotName/x.y.z`
   - Example: `CustomBot/1.0.0`

4. **Bot Keywords** (Medium confidence: +0.35)
   - Keywords: bot, crawler, spider, scraper
   - Case-insensitive matching

### Confidence Scoring

- **High Confidence (≥0.7)**: Strong indicators present (compatible pattern, documentation URL, or multiple indicators)
- **Medium Confidence (0.5-0.69)**: Single strong indicator or multiple weak indicators
- **Low Confidence (<0.5)**: Weak indicators only, not classified as bot

### Test Coverage

Comprehensive test suite in `tests/TestHeuristicDetector.php`:

- ✓ Detects compatible bot pattern
- ✓ Detects bot with documentation URL
- ✓ Extracts bot name from patterns
- ✓ Scores confidence based on indicators
- ✓ Returns high confidence for clear patterns
- ✓ Detects bot keywords (bot, crawler, spider, scraper)
- ✓ Detects version number pattern
- ✓ Detects documentation URL pattern
- ✓ Does not detect regular browsers
- ✓ Handles empty user agent
- ✓ Case insensitive detection
- ✓ Returns valid detection result

### Example Usage

```php
$detector = new TA_Heuristic_Detector();
$result = $detector->detect( 'Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)' );

if ( $result->is_bot() && $result->get_confidence() >= 0.7 ) {
    $bot_name = $result->get_bot_name(); // "Googlebot"
    $indicators = $result->get_indicators(); // array('compatible_pattern', 'documentation_url', 'keyword_bot', 'version_pattern')
}
```

## Development

### Running Tests

```bash
cd third-audience
vendor/bin/phpunit tests/TestHeuristicDetector.php --testdox
```

### Adding New Detectors

1. Create a new detector class in this directory
2. Create corresponding test file in `tests/`
3. Update `tests/bootstrap.php` to load the new detector
4. Follow TDD: write tests first, then implement
