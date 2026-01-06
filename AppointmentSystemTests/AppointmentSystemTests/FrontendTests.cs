using OpenQA.Selenium;
using OpenQA.Selenium.Chrome;
using OpenQA.Selenium.Support.UI;
using SeleniumExtras.WaitHelpers;

namespace AppointmentSystemTests;

public class FrontendTests
{
    private IWebDriver _driver = null!;
    private WebDriverWait _wait = null!;

    private const string BaseUrl = "http://frontend.vm1.test";
    private const string LogInUrl = "http://frontend.vm1.test/login";
    private const string RegisterUrl = "http://frontend.vm1.test/singup";
    private const string AppointmentUrl = "http://frontend.vm1.test/booking";
    private const string BarbersUrl = "http://frontend.vm1.test/barbers";
    private const string ContactUrl = "http://frontend.vm1.test/contact";

    [SetUp]
    public void Setup()
    {
        var options = new ChromeOptions();

        options.AddArgument("--start-maximized");

        _driver = new ChromeDriver(options);
        _wait = new WebDriverWait(_driver, TimeSpan.FromSeconds(10));

    }

    [Test]
    public void PagesAreReachable()
    {
        var urlsToCheck = new[]
            {
                BaseUrl,
                LogInUrl,
                RegisterUrl,
                AppointmentUrl,
                BarbersUrl,
                ContactUrl
            };

        Assert.Multiple(() =>
        {
            foreach (var url in urlsToCheck)
            {
                _driver.Navigate().GoToUrl(url);
                Assert.That(_driver.Url, Does.Contain(url), "Nem a megfelelő URL töltődött be.");
            }
        });
    }

    [TestCase("//*[contains(text(), 'See More')]", BarbersUrl, "See More Gomb")]
    [TestCase("//nav//*[contains(text(), 'Book Now')] | //header//*[contains(text(), 'Book Now')]", AppointmentUrl, "Header Book Now")]
    [TestCase("//button[contains(text(), 'Book your appointment')] | //a[contains(text(), 'Book your appointment')]", AppointmentUrl, "Main Book Appointment")]
    public void Button_ShouldNavigate_ToCorrectPage(string xpathSelector, string expectedUrlPart, string testName)
    {
        _driver.Navigate().GoToUrl(BaseUrl);
        try
        {
            var button = _wait.Until(ExpectedConditions.ElementToBeClickable(By.XPath(xpathSelector)));
            button.Click();
        }
        catch (WebDriverTimeoutException)
        {
            Assert.Fail($"Hiba ({testName}): Nem található vagy nem kattintható a gomb ezzel az XPath-szal: {xpathSelector}");
        }
        try
        {
            var urlChanged = _wait.Until(d => d.Url.Contains(expectedUrlPart));
            Assert.That(urlChanged, Is.True,
                $"Hiba ({testName}): A gomb nem a '{expectedUrlPart}' oldalra vitt, hanem ide: {_driver.Url}");
        }
        catch (WebDriverTimeoutException)
        {
            Assert.Fail($"Hiba ({testName}): Időtúllépés az átirányításnál. Az URL nem tartalmazta ezt: {expectedUrlPart}");
        }
    }

    [TearDown]
    public void Teardown()
    {
        _driver.Quit();
        _driver.Dispose();
    }
}
