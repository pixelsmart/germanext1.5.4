function isPrivacyChecked()
{
    return $('#secure').length == 0 || $('#secure:checked').length == 1;
}