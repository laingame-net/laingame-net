FROM debian

RUN apt-get install git npm php compose lftp
RUN mkdir /laingame
COPY build.sh /build.sh

ENTRYPOINT [ "/build.sh" ]