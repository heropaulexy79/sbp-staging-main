# Vector Database Setup Guide

This guide explains how to set up the hybrid RAG (Retrieval-Augmented Generation) system using MySQL for main data storage and libSQL for vector embeddings.

## Architecture Overview

- **MySQL**: Stores main application data including knowledge base content
- **libSQL**: Stores vector embeddings for semantic search and similarity matching
- **HybridRagService**: Coordinates between both databases for AI-powered content generation

## Prerequisites

1. **libSQL Installation**: Install libSQL on your system
2. **Gemini API Key**: Get an API key from Google AI Studio
3. **Laravel Environment**: Ensure your Laravel application is properly configured

## Setup Steps

### 1. Environment Configuration

Add these variables to your `.env` file:

```env
# libSQL Vector Database Configuration
LIBSQL_DATABASE=/path/to/your/vectors.libsql

# Gemini AI Configuration
GEMINI_API_KEY=your_gemini_api_key_here
GEMINI_MODEL=gemini-1.5-flash
```

### 2. Install libSQL

#### Option A: Download Binary
```bash
# Download libSQL binary for your platform
curl -L https://github.com/libsql/libsql/releases/latest/download/libsql-linux-x86_64.tar.gz | tar -xz
sudo mv libsql /usr/local/bin/
```

#### Option B: Using Docker
```bash
# Add to your docker-compose.yml
libsql:
  image: libsql/libsql:latest
  volumes:
    - ./data/libsql:/data
  ports:
    - "8080:8080"
```

### 3. Run Database Migrations

```bash
# Run MySQL migrations (your existing database)
php artisan migrate

# Run libSQL migrations
php artisan migrate --database=libsql
```

### 4. Setup Vector Database

```bash
# Run the setup command
php artisan rag:setup-vectors
```

This command will:
- Test both database connections
- Verify Gemini API configuration
- Run libSQL migrations
- Sync existing knowledge base entries to vector database

### 5. Verify Setup

```bash
# Test the RAG system
curl -X GET http://your-app.com/api/rag/test
```

Expected response:
```json
{
  "success": true,
  "message": "Hybrid RAG system is ready",
  "data": {
    "gemini_configured": true,
    "mysql_knowledge_entries": 4,
    "libsql_vector_entries": 4,
    "sync_status": "synced"
  }
}
```

## Usage

### Generate AI Content

```bash
curl -X POST http://your-app.com/api/rag/generate-content \
  -H "Content-Type: application/json" \
  -d '{
    "title": "Introduction to Machine Learning",
    "course_id": 1,
    "options": {
      "difficulty": "beginner",
      "length": "medium"
    }
  }'
```

### Add Knowledge Base Entry

```bash
curl -X POST http://your-app.com/api/rag/add-knowledge \
  -H "Content-Type: application/json" \
  -d '{
    "title": "Advanced Python Concepts",
    "content": "Python decorators, generators, and context managers...",
    "source_type": "external",
    "keywords": "python, programming, advanced, decorators"
  }'
```

### Search Knowledge Base

```bash
curl -X GET "http://your-app.com/api/rag/search?query=machine%20learning&limit=5"
```

### Sync Knowledge Base

```bash
curl -X POST http://your-app.com/api/rag/sync
```

## API Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/api/rag/generate-content` | Generate lesson content using RAG |
| POST | `/api/rag/add-knowledge` | Add new knowledge base entry |
| GET | `/api/rag/search` | Search knowledge base semantically |
| POST | `/api/rag/log-generation` | Log AI generation history |
| GET | `/api/rag/test` | Test system status |
| POST | `/api/rag/sync` | Sync knowledge base to vectors |

## Troubleshooting

### Common Issues

1. **libSQL Connection Failed**
   - Ensure libSQL is installed and accessible
   - Check the `LIBSQL_DATABASE` path in your `.env` file
   - Verify file permissions

2. **Gemini API Errors**
   - Verify your `GEMINI_API_KEY` is correct
   - Check API quota and billing
   - Ensure the model name is valid

3. **Vector Sync Issues**
   - Run `php artisan rag:setup-vectors` to resync
   - Check database permissions
   - Verify both MySQL and libSQL connections

### Debug Commands

```bash
# Check database connections
php artisan tinker
>>> DB::connection('mysql')->getPdo();
>>> DB::connection('libsql')->getPdo();

# Test vector operations
php artisan tinker
>>> $service = app(\App\Services\HybridRagService::class);
>>> $service->searchKnowledgeBase('test query');
```

## Performance Considerations

- **Vector Search**: libSQL provides fast similarity search with cosine similarity
- **Embedding Generation**: Uses Gemini's embedding-001 model (1536 dimensions)
- **Batch Operations**: Sync operations process entries in batches for efficiency
- **Caching**: Consider implementing Redis caching for frequently accessed vectors

## Security Notes

- Store API keys securely in environment variables
- Use HTTPS for API endpoints in production
- Implement rate limiting for AI generation endpoints
- Consider API key rotation for enhanced security

## Monitoring

Monitor these metrics:
- Vector database size and growth
- API usage and costs
- Search performance and accuracy
- Sync operation success rates

## Next Steps

1. **Implement Caching**: Add Redis caching for vector search results
2. **Batch Processing**: Implement background jobs for large sync operations
3. **Analytics**: Add usage tracking and performance metrics
4. **UI Integration**: Build frontend components for knowledge base management
5. **Advanced Features**: Implement multi-modal embeddings and advanced RAG techniques
