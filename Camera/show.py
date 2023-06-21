import cv2
import datetime
import os
import requests
import glob
from dotenv import load_dotenv


cap = cv2.VideoCapture(-1)
mog = cv2.createBackgroundSubtractorMOG2()
motion_detected = False
output_file = None
last_motion_time = datetime.datetime.now()
last_break_time = datetime.datetime.now()
absolute_path = '/videos'

# CONFIG
load_dotenv()
buffer_time = int(os.getenv('BUFFER_TIME', '25'))  # Buffer time (in seconds) between motion segments
min_break_time = int(os.getenv('MIN_BREAK_TIME', '60'))  # Minimum break time (in seconds) to create a new file
clear_videos = os.getenv('CLEAR_VIDEOS')  # Check if the flag to clear leftover videos is set to 1
base_url = os.getenv('SERVER_URL')
username = os.getenv('USERNAME')
password = os.getenv('PASSWORD')
camera_id = os.getenv('CAMERA_ID')
send_videos_key = os.getenv('SEND_VIDEOS_KEY')
delete_videos_key = os.getenv('DELETE_VIDEOS_KEY')

if clear_videos == '1':
    # Clear leftover videos
    files = glob.glob(os.path.join(absolute_path, '*.avi'))
    for file in files:
        os.remove(file)
    print("Cleared leftover videos.")

while True:
    ret, frame = cap.read()
    gray = cv2.cvtColor(frame, cv2.COLOR_BGR2GRAY)
    fgmask = mog.apply(gray)
    kernel = cv2.getStructuringElement(cv2.MORPH_ELLIPSE, (5, 5))
    fgmask = cv2.erode(fgmask, kernel, iterations=1)
    fgmask = cv2.dilate(fgmask, kernel, iterations=1)

    contours, hierarchy = cv2.findContours(fgmask, cv2.RETR_EXTERNAL, cv2.CHAIN_APPROX_SIMPLE)

    motion_detected = False

    for contour in contours:
        # Ignore small contours
        if cv2.contourArea(contour) < 1000:
            continue

        x, y, w, h = cv2.boundingRect(contour)
        cv2.rectangle(frame, (x, y), (x + w, y + h), (0, 255, 0), 2)

        motion_detected = True
        last_motion_time = datetime.datetime.now()

    if motion_detected:
        # If motion detected within buffer time, save the frame to the output file
        if output_file is None or (datetime.datetime.now() - last_motion_time).total_seconds() > buffer_time:
            # Create a new file for recording
            timestamp = last_motion_time.strftime("%Y%m%d_%H%M%S")
            output_file = os.path.join(absolute_path, f"motion_{timestamp}.avi")
            out = cv2.VideoWriter(output_file, cv2.VideoWriter_fourcc(*'XVID'), 20.0, (frame.shape[1], frame.shape[0]))

    if motion_detected and out is not None:
        # Save the frame to the output file
        # Add current date and hour at the bottom of the frame
        current_date = datetime.datetime.now().strftime("%Y-%m-%d")
        current_hour = datetime.datetime.now().strftime("%H:%M:%S")
        text = f"{current_date} {current_hour}"
        cv2.putText(frame, text, (10, frame.shape[0] - 10),
                    cv2.FONT_HERSHEY_SIMPLEX, 0.7, (0, 0, 255), 2, cv2.LINE_AA)
        out.write(frame)

    cv2.imshow('Motion Detection', frame)

    key = cv2.waitKey(1)
    if key == ord(send_videos_key):
        print("send-videos pressed; starting to send...")
        # Get a list of all video files in the directory
        video_files = glob.glob(os.path.join(absolute_path, '*.avi'))

        # Iterate over each video file and send it as a request
        for video_file in video_files:
            # Create the URL for uploading the video
            upload_url = f"{base_url}"

            # Set the user login credentials and camera ID in the request data
            data = {
                'username': username,
                'password': password,
                'camera_id': camera_id
            }

            # Open the video file in binary mode
            with open(video_file, 'rb') as file:
                # Add the video file to the request data as a file object
                files = {'video': file}

                try:
                    # Define the headers with the Accept header
                    headers = {'Accept': 'application/json'}

                    # Send the request with the data, files, and headers, specifying a timeout
                    response = requests.post(upload_url, data=data, files=files, headers=headers, timeout=20)

                    # Check the response status code
                    if response.status_code == 200:
                        print(f"Video {video_file} uploaded successfully.")

                        # Remove the video file if successfully sent
                        os.remove(video_file)
                        print(f"Video {video_file} removed.")
                    else:
                        print(f"Failed to upload video {video_file}. Error: {response.text}")

                except requests.Timeout:
                    print(f"No response for {video_file} after 20 seconds. Skipping...")
                except Exception as e:
                    print(f"Error {e} for {video_file} after 20 seconds. Skipping...")
        break

    elif key == ord(delete_videos_key):
        files = glob.glob(os.path.join(absolute_path, '*.avi'))
        for file in files:
            os.remove(file)
        print("Clear-videos pressed; clearing videos")
        break

    # Check if the break time exceeds the minimum break time
    if not motion_detected and output_file is not None and (
            datetime.datetime.now() - last_motion_time).total_seconds() > min_break_time:
        out.release()
        output_file = None
        last_break_time = datetime.datetime.now()

# Release the video capture and video writer objects
cap.release()
if output_file:
    out.release()

cv2.destroyAllWindows()

