```mermaid
flowchart TD
    subgraph Browser
        UI[User Interface (React)]
    end
    subgraph Frontend
        UI -->|API Calls| APIWrapper[Axios Service]
    end
    subgraph Backend
        APIWrapper -->|REST| Server[Express Server]
        Server --> Auth[JWT Auth Middleware]
        Server --> Controllers[Controller Layer]
        Controllers --> Services[Service Layer]
        Services --> DB[PostgreSQL]
    end
    DB -->|Migrations| Migration[SQL Init & Seed]
    style Browser fill:#f9f,stroke:#333,stroke-width:2px
    style Frontend fill:#bbf,stroke:#333,stroke-width:2px
    style Backend fill:#bfb,stroke:#333,stroke-width:2px
    style DB fill:#ff9,stroke:#333,stroke-width:2px
```

