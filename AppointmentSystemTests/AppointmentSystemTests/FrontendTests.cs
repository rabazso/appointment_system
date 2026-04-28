namespace AppointmentSystemTests;

public class FrontendTests : Base
{
    private const string AppointmentUrl = "http://frontend.vm1.test/booking";
    private const string BarbersUrl = "http://frontend.vm1.test/barbers";
    private const string ContactUrl = "http://frontend.vm1.test/contact";

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
                driver.Navigate().GoToUrl(url);
                Assert.That(driver.Url, Does.Contain(url), "Nem a megfelelő URL töltődött be.");
            }
        });
    }
}
