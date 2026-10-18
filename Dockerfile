# فقط برای نسخه نمایشی روی Railway. هاست اصلی (دایرکت‌ادمین) به این فایل نیازی ندارد.
FROM php:8.3-cli
WORKDIR /app
COPY . .
ENV TZ=Asia/Tehran
EXPOSE 8080
CMD ["sh", "docker/start.sh"]
