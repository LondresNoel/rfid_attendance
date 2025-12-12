import serial, requests, time

PORT = "COM5"
BAUD = 9600
php_scan_url = "http://localhost/rfid_attendance/read_rfid.php"
php_date_url = "http://localhost/rfid_attendance/active_date.php"

ser = serial.Serial(PORT, BAUD, timeout=1)
print("Listening for RFID tags on", PORT)

while True:
    try:
        line = ser.readline().decode().strip()
        if line:

            # Accept Arduino format: "Scanned: 12 AB 4F 88"
            if "Scanned:" in line:
                tag_raw = line.replace("Scanned:", "").strip()

                # DO NOT REMOVE SPACES — KEEP EXACT UID
                tag = tag_raw  
                print("Scanned:", tag)

                # get active date from PHP
                try:
                    resp = requests.get(php_date_url, timeout=2)
                    active_date = resp.json().get("active_date", time.strftime("%Y-%m-%d"))
                except Exception as e:
                    print("Error getting active date:", e)
                    active_date = time.strftime("%Y-%m-%d")

                # send to PHP
                try:
                    r = requests.get(php_scan_url, params={"tag": tag, "date": active_date}, timeout=3)
                    print("Server:", r.text)
                except Exception as e:
                    print("Error sending to PHP:", e)

        time.sleep(0.2)

    except Exception as e:
        print("Serial error:", e)
        time.sleep(1)
