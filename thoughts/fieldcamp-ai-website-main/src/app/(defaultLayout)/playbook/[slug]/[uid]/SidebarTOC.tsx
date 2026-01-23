'use client';

import React, { useState, useEffect } from 'react';

interface PlaybookPage {
  id: string;
  title: string;
  uri: string;
  date?: string;
}

interface LabelCategory {
  id: string;
  name: string;
  parent?: {
    node: {
      id: string;
      name: string;
    };
  };
  playbooks: {
    nodes: PlaybookPage[];
  };
}

interface SidebarTOCProps {
  labelCategories?: {
    nodes: LabelCategory[];
  };
  currentUri?: string;
  parentSlug?: string;
}

export default function SidebarTOC({ labelCategories, currentUri, parentSlug }: SidebarTOCProps) {
  const [expandedCategories, setExpandedCategories] = useState<Set<string>>(new Set());
  const [hasInitialized, setHasInitialized] = useState(false);

  // Helper to check if category belongs to AI Dispatching family
  const isAiDispatchFamily = (category: LabelCategory) => {
    const aiDispatchNames = ["AI Dispatching Guide", "ai-dispatching-guide"];

    // Check if this category name matches any AI Dispatch family names
    const isCategoryMatch = aiDispatchNames.includes(category.name);

    // Check if parent name matches any AI Dispatch family names
    const isParentMatch = category.parent?.node?.name &&
                          aiDispatchNames.includes(category.parent.node.name);

    return isCategoryMatch || isParentMatch;
  };

  // Helper to check if category has any playbooks assigned
  const hasPlaybooks = (category: LabelCategory) => {
    return category.playbooks?.nodes && category.playbooks.nodes.length > 0;
  };

  // Filter categories based on parent slug
  const filterLabelCategories = (labelCats: typeof labelCategories) => {
    if (!labelCats?.nodes) return labelCats;

    const isAiDispatchingParent = parentSlug === "ai-dispatching";

    if (isAiDispatchingParent) {
      // Show only AI Dispatching Guide and its children that have playbooks
      return {
        nodes: labelCats.nodes.filter(category =>
          isAiDispatchFamily(category) && hasPlaybooks(category)
        )
      };
    }

    // Hide AI Dispatching Guide and its children, show all others that have playbooks
    return {
      nodes: labelCats.nodes.filter(category =>
        !isAiDispatchFamily(category) && hasPlaybooks(category)
      )
    };
  };

  // Process labelCategories to ensure playbooks appear in all their categories
  const processLabelCategories = (labelCats: typeof labelCategories) => {
    if (!labelCats?.nodes) return labelCats;
    
    // Collect all unique playbooks from all categories
    const allPlaybooks = new Map<string, PlaybookPage>();
    const categoryPlaybookMap = new Map<string, Set<string>>();
    
    // First, collect all playbooks and their category associations
    labelCats.nodes.forEach((category: LabelCategory) => {
      categoryPlaybookMap.set(category.id, new Set());
      if (category.playbooks?.nodes) {
        category.playbooks.nodes.forEach((playbook: PlaybookPage) => {
          allPlaybooks.set(playbook.id, playbook);
          categoryPlaybookMap.get(category.id)?.add(playbook.id);
        });
      }
    });
    
    // Don't force current playbook into all categories - let it appear naturally
    // This preserves the original data structure and prevents toggle interference
    
    // Rebuild the structure with playbooks sorted by latest published date
    return {
      nodes: labelCats.nodes.map((category: LabelCategory) => ({
        ...category,
        playbooks: {
          nodes: Array.from(categoryPlaybookMap.get(category.id) || [])
            .map(playbookId => allPlaybooks.get(playbookId))
            .filter((playbook): playbook is PlaybookPage => playbook !== undefined)
            .sort((a, b) => {
              // Sort by date descending (latest first)
              const dateA = a.date ? new Date(a.date).getTime() : 0;
              const dateB = b.date ? new Date(b.date).getTime() : 0;
              return dateB - dateA;
            })
        }
      }))
    };
  };

  // First filter, then process
  const filteredLabelCategories = filterLabelCategories(labelCategories);
  const processedLabelCategories = processLabelCategories(filteredLabelCategories);

  // Auto-expand only categories that contain the active page (only on initial load)
  useEffect(() => {
    // Only run this on initial load, not on subsequent re-renders
    if (hasInitialized) return;

    if (currentUri && filteredLabelCategories?.nodes) {
      const categoriesToExpand = new Set<string>();

      // Normalize URIs for comparison (remove trailing slashes)
      const normalizeUri = (uri: string) => uri.replace(/\/+$/, '');
      const normalizedCurrentUri = normalizeUri(currentUri);

      filteredLabelCategories.nodes.forEach((category: LabelCategory) => {
        if (category.playbooks?.nodes) {

          const hasActivePage = category.playbooks.nodes.some(
            (playbook: PlaybookPage) => normalizeUri(playbook.uri) === normalizedCurrentUri
          );

          if (hasActivePage) {
            categoriesToExpand.add(category.id);
          }
        }
      });
      // Only expand categories that contain the active page
      setExpandedCategories(categoriesToExpand);
      setHasInitialized(true);
    } else {
      // If no currentUri, keep all categories collapsed
      setExpandedCategories(new Set());
      setHasInitialized(true);
    }
  }, [currentUri, hasInitialized, filteredLabelCategories]);

  // Don't render if no label categories
  if (!labelCategories?.nodes || labelCategories.nodes.length === 0) {
    return null;
  }

  const toggleCategory = (categoryId: string) => {
    setExpandedCategories(prev => {
      const newSet = new Set(prev);
      if (newSet.has(categoryId)) {
        newSet.delete(categoryId);
      } else {
        newSet.add(categoryId);
      }
      return newSet;
    });
  };

  return (
    <div className="wp-block-group sidebar-navigation is-layout-flow wp-block-group-is-layout-flow">
      {processedLabelCategories?.nodes?.map((category: LabelCategory) => {
        const isExpanded = expandedCategories.has(category.id);
        const hasPlaybooks = category.playbooks?.nodes && category.playbooks.nodes.length > 0;
        
        // Check if this category contains the active page
        const normalizeUri = (uri: string) => uri.replace(/\/+$/, '');
        const hasActivePage = currentUri && category.playbooks?.nodes?.some(
          (playbook: PlaybookPage) => normalizeUri(playbook.uri) === normalizeUri(currentUri)
        );

        return (
          <div key={category.id} className={`nav-category ${isExpanded ? 'open' : ''}`}>
            <button 
              className={`nav-toggle ${hasActivePage ? 'active' : ''}`}
              onClick={() => toggleCategory(category.id)}
              aria-expanded={isExpanded}
              aria-controls={`nav-dropdown-${category.id}`}
            >
              <span>{category.name}</span>
              <svg 
                className={`chevron ${isExpanded ? 'expanded' : ''}`}
                width="12" 
                height="12" 
                viewBox="0 0 12 12"
              >
                <path 
                  d="M3 4.5L6 7.5L9 4.5" 
                  stroke="currentColor" 
                  strokeWidth="2" 
                  fill="none" 
                />
              </svg>
            </button>
            
            {isExpanded && hasPlaybooks && (
              <ul 
                className="nav-dropdown"
                id={`nav-dropdown-${category.id}`}
              >
                {category.playbooks.nodes.map((playbook: PlaybookPage) => {
                  const normalizeUri = (uri: string) => uri.replace(/\/+$/, '');
                  const normalizedCurrentUri = currentUri ? normalizeUri(currentUri) : '';
                  const normalizedPlaybookUri = normalizeUri(playbook.uri);
                  const isActive = normalizedCurrentUri === normalizedPlaybookUri;
                  
                  return (
                    <li key={playbook.id} className={isActive ? 'active' : ''}>
                      <a 
                        href={playbook.uri}
                        className={isActive ? 'active' : ''}
                      >
                        {playbook.title}
                      </a>
                    </li>
                  );
                })}
              </ul>
            )}
          </div>
        );
      })}
    </div>
  );
}