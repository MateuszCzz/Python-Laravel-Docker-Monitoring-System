import cv2

def main():
    # Create a VideoCapture object
    cap = cv2.VideoCapture(-1)

    # Check if the camera is opened successfully
    if not cap.isOpened():
        print("Unable to open the camera")
        return

    while True:
        # Read a frame from the camera
        ret, frame = cap.read()

        # Check if the frame is empty
        if not ret:
            print("Unable to receive frame from the camera")
            break

        # Display the frame in a window
        cv2.imshow("Camera", frame)

        # Exit the loop if 'q' is pressed
        if cv2.waitKey(1) & 0xFF == ord('q'):
            break

    # Release the VideoCapture object and close the window
    cap.release()
    cv2.destroyAllWindows()

if __name__ == "__main__":
    main()

