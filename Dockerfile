FROM dzentota/phpantom:0.4
MAINTAINER Alex Tatulchenkov <webtota@gmail.com>
ENV HOME /home/phpantom
ADD . /home/phpantom
RUN chown -R phpantom:phpantom /home/phpantom
USER phpantom

WORKDIR /home/phpantom

CMD ["/usr/bin/php"]