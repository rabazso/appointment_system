using OpenQA.Selenium;
using SeleniumExtras.WaitHelpers;

namespace AppointmentSystemTests.HeroAuthCoice
{
    public class NoSignInTests : Base
    {
        [Test]
        public void BookNow_WhenNotLoggedIn_ShouldShowAuthChoiceModal()
        {
            ClickTestId("book-now-btn");

            var guestButton = VisibleTestId("guest-btn");

            Assert.That(guestButton.Displayed, Is.True);
        }

        [Test]
        public void SignInBtnThroughHeaderShouldShowAuthChoiceModal()
        {
            ClickTestId("headerbtn");

            var modal = VisibleTestId("auth-modal");

            Assert.That(modal.Displayed, Is.True);
        }

        [Test]
        public void ContinueAsGuest_ShouldNavigateToBooking()
        {
            ClickTestId("book-now-btn");

            ClickTestId("guest-btn");

            wait.Until(ExpectedConditions.UrlContains("/booking"));

            Assert.That(driver.Url, Does.Contain("/booking"));
        }
    }
}
