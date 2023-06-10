FROM python:3.6.9-slim

# install dependencies
RUN export DEBIAN_FRONTEND=noninteractive && \
    apt-get update && \
    apt-get -y upgrade && \
    apt-get install -y --no-install-recommends \
      xcb \
      libglib2.0-0 \
      libgl1-mesa-glx \
      libxrender1 && \
    apt-get -y clean && \
    rm -rf /var/lib/apt/lists/*

# Setting up working directory
RUN mkdir /src
WORKDIR /src
COPY requirements.txt requirements.txt
COPY show.py .

# Minimize image size
RUN pip install --upgrade pip
RUN pip install --no-cache-dir -r requirements.txt

ENV QT_X11_NO_MITSHM=1
CMD ["python", "show.py"]

