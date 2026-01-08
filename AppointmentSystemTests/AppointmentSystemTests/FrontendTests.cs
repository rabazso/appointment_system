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

    [TearDown]
    public void Teardown()
    {
        _driver.Quit();
        _driver.Dispose();
    }
}
