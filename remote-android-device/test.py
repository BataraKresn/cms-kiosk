import base64
import requests

# Fetch the content of the URL
url = "https://kiosk.penerbitan-dpr.id/display/196IMoMt8hEqs6Qyz7DW4oOHsR6yKle88qhu0hA10OTyAUsWVeUkp3RDffboofloIbbgzZWgl4MiqBnn20231113091510"
response = requests.get(url)

# Convert the content to Base64
if response.status_code == 200:
    base64_content = base64.b64encode(response.content).decode("utf-8")
    print("Base64 Content:")
    print(base64_content)
else:
    print("Failed to fetch content:", response.status_code)
