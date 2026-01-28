module.exports = {
  apps: [
    {
      name: 'generate-pdf',
      script: 'index.js',
      instances: 1,
      autorestart: true,
      watch: false,
      max_memory_restart: '1G',
      out_file: '/usr/src/app/logs/generate-pdf-out.log',
      error_file: '/usr/src/app/logs/generate-pdf-error.log',
      log_date_format: 'YYYY-MM-DD HH:mm:ss Z',
      merge_logs: true,
      env: {
        NODE_ENV: 'production'
      }
    },
    {
      name: 'websocket',
      script: 'websocket.js',
      instances: 1,
      autorestart: true,
      watch: false,
      max_memory_restart: '500M',
      out_file: '/usr/src/app/logs/websocket-out.log',
      error_file: '/usr/src/app/logs/websocket-error.log',
      log_date_format: 'YYYY-MM-DD HH:mm:ss Z',
      merge_logs: true,
      env: {
        NODE_ENV: 'production'
      }
    }
  ]
};
