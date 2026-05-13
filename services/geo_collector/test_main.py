import pytest
from main import analyze_sentiment

@pytest.mark.parametrize("text, expected_sentiment", [
    ("This is a wonderful and amazing product!", "positive"),
    ("I am very disappointed with the quality.", "negative"),
    ("The product is okay, not great but not bad.", "neutral"),
    ("This is just a statement of fact.", "neutral"),
    ("", "neutral"),
    (None, "neutral"),
])
def test_analyze_sentiment(text, expected_sentiment):
    """
    Tests the sentiment analysis function with various inputs.
    """
    assert analyze_sentiment(text) == expected_sentiment
